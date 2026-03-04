#!/usr/bin/env python
# -*- coding: utf-8 -*-

import argparse
import csv
import datetime as dt
import difflib
import json
import re
import subprocess
import sys
import unicodedata
from pathlib import Path

try:
    import pyodbc
except ImportError as exc:
    print("ERROR: falta pyodbc. Instala con: pip install pyodbc", file=sys.stderr)
    raise

PASSWORD_PLAIN = "12345678"


def read_env(env_path: Path) -> dict:
    data = {}
    if not env_path.exists():
        return data

    for raw in env_path.read_text(encoding="utf-8").splitlines():
        line = raw.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        k, v = line.split("=", 1)
        data[k.strip()] = v.strip().strip('"').strip("'")
    return data


def normalize_emails(raw: str) -> list[str]:
    if not raw:
        return []
    parts = re.split(r"[;,\s]+", raw.strip())
    out = []
    for p in parts:
        email = p.strip().lower()
        if not email:
            continue
        if re.match(r"^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$", email):
            out.append(email)
    return sorted(set(out))


def ensure_ges_suffix(name: str) -> str:
    name = (name or "").strip()
    if not name:
        name = "gestante"
    return name if name.lower().endswith("_ges") else f"{name}_ges"


def normalize_name(text: str) -> str:
    text = (text or "").strip().lower()
    text = unicodedata.normalize("NFKD", text)
    text = "".join(ch for ch in text if not unicodedata.combining(ch))
    text = re.sub(r"[^a-z0-9]+", " ", text)
    return re.sub(r"\s+", " ", text).strip()


def find_best_code_by_name(
    source_name: str,
    catalog: list[dict],
    min_score: float,
) -> tuple[str | None, str | None, float]:
    src = normalize_name(source_name)
    if not src:
        return None, None, 0.0

    best_code = None
    best_name = None
    best_score = 0.0

    for item in catalog:
        target = normalize_name(item["name"])
        if not target:
            continue

        score = difflib.SequenceMatcher(None, src, target).ratio()
        if src in target or target in src:
            score = max(score, 0.90)

        if score > best_score:
            best_score = score
            best_code = item["code"]
            best_name = item["name"]

    if best_score < min_score:
        return None, best_name, best_score
    return best_code, best_name, best_score


def make_bcrypt_with_php(password: str) -> str:
    cmd = [
        "php",
        "-r",
        f"echo password_hash('{password}', PASSWORD_BCRYPT), PHP_EOL;",
    ]
    result = subprocess.run(cmd, capture_output=True, text=True, check=True)
    return result.stdout.strip()


def connect_sqlserver(env: dict):
    host = env.get("DB_HOST", "127.0.0.1")
    port = (env.get("DB_PORT", "") or "").strip()
    database = env.get("DB_DATABASE", "")
    username = env.get("DB_USERNAME", "")
    password = env.get("DB_PASSWORD", "")

    server = f"{host},{port}" if port else host
    conn_str = (
        "DRIVER={ODBC Driver 17 for SQL Server};"
        f"SERVER={server};"
        f"DATABASE={database};"
        f"UID={username};PWD={password};"
        "TrustServerCertificate=yes;"
    )
    return pyodbc.connect(conn_str)


def main() -> int:
    parser = argparse.ArgumentParser(
        description="Crea usuarios gestante (usertype=2), omite emails existentes y genera reporte."
    )
    parser.add_argument(
        "--input",
        default="scripts/gestante_users_input.json",
        help="Ruta del JSON de entrada.",
    )
    parser.add_argument(
        "--env",
        default=".env",
        help="Ruta al archivo .env de Laravel.",
    )
    parser.add_argument(
        "--report-dir",
        default="storage/app/reports",
        help="Directorio de salida para reportes.",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Simula, no inserta registros en BD.",
    )
    parser.add_argument(
        "--min-score",
        type=float,
        default=0.62,
        help="Umbral minimo (0-1) de similitud para mapear codigo por nombre.",
    )
    args = parser.parse_args()

    input_path = Path(args.input)
    if not input_path.exists():
        print(f"ERROR: no existe archivo de entrada: {input_path}", file=sys.stderr)
        return 1

    try:
        rows = json.loads(input_path.read_text(encoding="utf-8-sig"))
    except Exception as exc:
        print(f"ERROR leyendo JSON: {exc}", file=sys.stderr)
        return 1

    if not isinstance(rows, list):
        print("ERROR: el JSON debe ser una lista de objetos.", file=sys.stderr)
        return 1

    env = read_env(Path(args.env))
    if not env.get("DB_DATABASE"):
        print("ERROR: no se pudo leer conexion DB desde .env", file=sys.stderr)
        return 1

    report_dir = Path(args.report_dir)
    report_dir.mkdir(parents=True, exist_ok=True)

    timestamp = dt.datetime.now().strftime("%Y%m%d_%H%M%S")
    csv_created = report_dir / f"usuarios_gestante_creados_{timestamp}.csv"
    csv_skipped = report_dir / f"usuarios_gestante_omitidos_{timestamp}.csv"
    txt_credentials = report_dir / f"credenciales_gestante_{timestamp}.txt"

    created_rows = []
    skipped_rows = []

    password_hash = make_bcrypt_with_php(PASSWORD_PLAIN)

    with connect_sqlserver(env) as conn:
        cur = conn.cursor()
        cur.execute(
            """
            SELECT name, codigohabilitacion
            FROM users
            WHERE codigohabilitacion IS NOT NULL
              AND LTRIM(RTRIM(codigohabilitacion)) <> ''
              AND codigohabilitacion NOT LIKE 'PEND_%'
            """
        )
        code_catalog = [{"name": r[0], "code": str(r[1]).strip()} for r in cur.fetchall()]

        for row in rows:
            ips = str(row.get("ips", "")).strip()
            base_name = ensure_ges_suffix(ips if ips else "gestante")
            code = str(row.get("codigohabilitacion", "")).strip()

            resolved_code = code
            matched_name = ""
            matched_score = 0.0
            if not resolved_code:
                resolved_code, matched_name, matched_score = find_best_code_by_name(
                    ips, code_catalog, args.min_score
                )

            emails_raw = row.get("emails") or row.get("email") or ""
            emails = normalize_emails(str(emails_raw))

            for email in emails:
                cur.execute("SELECT id FROM users WHERE email = ?", email)
                exists = cur.fetchone() is not None

                if exists:
                    skipped_rows.append(
                        {
                            "email": email,
                            "motivo": "YA_EXISTE",
                            "name": "",
                            "password": "",
                            "usertype": "",
                            "codigohabilitacion": "",
                        }
                    )
                    continue

                if not resolved_code:
                    skipped_rows.append(
                        {
                            "email": email,
                            "motivo": f"NO_CODIGO_ENCONTRADO (match={matched_name or 'N/A'} score={matched_score:.3f})",
                            "name": base_name,
                            "password": "",
                            "usertype": 2,
                            "codigohabilitacion": "",
                        }
                    )
                    continue

                final_code = resolved_code
                now = dt.datetime.now().strftime("%Y-%m-%d %H:%M:%S")

                if not args.dry_run:
                    cur.execute(
                        """
                        INSERT INTO users
                        (name, email, email_verified_at, password, usertype, codigohabilitacion, remember_token, created_at, updated_at)
                        VALUES (?, ?, NULL, ?, ?, ?, NULL, ?, ?)
                        """,
                        base_name,
                        email,
                        password_hash,
                        2,
                        final_code,
                        now,
                        now,
                    )

                created_rows.append(
                    {
                        "email": email,
                        "name": base_name,
                        "password": PASSWORD_PLAIN,
                        "usertype": 2,
                        "codigohabilitacion": final_code,
                        "matched_name": matched_name,
                        "matched_score": f"{matched_score:.3f}",
                    }
                )

        if not args.dry_run:
            conn.commit()

    with csv_created.open("w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(
            f,
            fieldnames=[
                "email",
                "name",
                "password",
                "usertype",
                "codigohabilitacion",
                "matched_name",
                "matched_score",
            ],
        )
        writer.writeheader()
        writer.writerows(created_rows)

    with csv_skipped.open("w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(
            f,
            fieldnames=["email", "motivo", "name", "password", "usertype", "codigohabilitacion"],
        )
        writer.writeheader()
        writer.writerows(skipped_rows)

    with txt_credentials.open("w", encoding="utf-8") as f:
        f.write("USUARIOS CREADOS - MODULO GESTANTE\n")
        f.write("Contrasena por defecto: 12345678\n\n")
        for r in created_rows:
            f.write(
                f"{r['email']} | {r['name']} | pass: {r['password']} | code: {r['codigohabilitacion']}\n"
            )

    print("Proceso completado.")
    print(f"Creados: {len(created_rows)}")
    print(f"Omitidos (ya existian): {len(skipped_rows)}")
    print(f"Reporte creados: {csv_created}")
    print(f"Reporte omitidos: {csv_skipped}")
    print(f"Reporte credenciales: {txt_credentials}")
    if args.dry_run:
        print("Modo dry-run activo: no se insertaron registros.")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
