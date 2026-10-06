#!/usr/bin/env python3
"""Small repository/import guard. No dependency install, bootstrap or deployment.

Checks files intended for Git, selected runtime configuration and available
syntax/Composer validation tools. This is not a complete security audit.
Diagnostics contain rule names and paths, never matched secret values.
"""
from __future__ import annotations

import argparse
import json
import os
from pathlib import Path
import re
import shutil
import subprocess
import sys

ROOT = Path(__file__).resolve().parents[1]
SKIP_DIRS = {".git", "vendor", "node_modules", ".cache", ".runtime", "private",
             "backups", "snapshots", "var", "build", "dist", "__pycache__"}
GENERATED = ("web/core/", "web/modules/contrib/", "web/themes/contrib/",
             "web/profiles/contrib/", "web/sites/default/files/")
SAFE_SETTINGS = "web/sites/default/settings.php"
PRODUCTION = re.compile(r"neruds[.]org", re.IGNORECASE)
TEXT_EXTENSIONS = {".php", ".inc", ".module", ".install", ".theme", ".js",
                   ".ts", ".json", ".yaml", ".yml", ".md", ".txt", ".py"}
SECRET_PATTERNS = {
    "private-key-content": re.compile(
        r"-----BEGIN (?:RSA |EC |OPENSSH |DSA )?PRIVATE KEY-----"),
    "github-token-content": re.compile(
        r"\b(?:gh[pousr]_[A-Za-z0-9]{32,}|github_pat_[A-Za-z0-9_]{40,})\b"),
    "cloud-key-content": re.compile(r"\b(?:AKIA|ASIA)[A-Z0-9]{16}\b"),
    "credential-in-url": re.compile(
        r"\b(?:https?|mysql|postgres(?:ql)?|mongodb(?:\+srv)?)://"
        r"[^/\s\"'<>:]+:[^/\s\"'<>@]+@",
        re.IGNORECASE),
}


def git_candidates(root: Path) -> list[Path]:
    """Include tracked files even if force-added against .gitignore."""
    if shutil.which("git"):
        probe = subprocess.run(
            ["git", "-C", str(root), "rev-parse", "--show-toplevel"],
            capture_output=True, text=True, check=False,
        )
        if probe.returncode == 0 and Path(probe.stdout.strip()).resolve() == root:
            result = subprocess.run(
                ["git", "-C", str(root), "ls-files", "--cached", "--others",
                 "--exclude-standard", "-z"],
                capture_output=True, check=True,
            )
            return sorted({
                root / os.fsdecode(name)
                for name in result.stdout.split(b"\0") if name
            })
    # Import preparation may precede git init. Runtime/dependency directories and
    # the legitimate untracked local .env are excluded from this fallback.
    found = []
    for directory, folders, names in os.walk(root, followlinks=False):
        for name in folders:
            link = Path(directory) / name
            if link.is_symlink():
                found.append(link)
        folders[:] = [
            name for name in folders
            if name not in SKIP_DIRS
            and not (Path(directory) / name).is_symlink()
            and not any(
                ((Path(directory) / name).relative_to(root).as_posix() + "/").startswith(
                    prefix
                ) for prefix in GENERATED
            )
        ]
        for name in names:
            if Path(directory) == root and name == ".env":
                continue
            found.append(Path(directory) / name)
    return sorted(found)


def blocked_path(relative: str) -> str | None:
    path = Path(relative)
    lower = path.name.lower()
    parts = {part.lower() for part in path.parts}
    if lower.startswith(".env") and lower != ".env.example":
        return "real-env-file"
    if lower in {"auth.json", "id_rsa", "id_ed25519", ".htpasswd"}:
        return "credential-file"
    if re.search(r"\.(sql|dump)(\.|$)|\.(sqlite3?|db|pem|key|p12|pfx)$", lower):
        return "database-or-key-file"
    if lower.endswith((".tar", ".tar.gz", ".tgz", ".zip", ".7z")):
        return "unreviewed-archive"
    if parts & {".ssh", ".aws", "private", "backups", "snapshots"}:
        return "private-runtime-path"
    if relative.startswith("web/sites/") and ("files" in parts or "private" in parts):
        return "runtime-content-in-git"
    if re.match(r"^settings(?:[._-]|$)", lower) and ".php" in lower:
        if relative != SAFE_SETTINGS and not lower.endswith(".php.example"):
            return "unapproved-settings-file"
    return None


def read_text(path: Path) -> str | None:
    # Binary/large artifacts are not treated as completely scanned.
    if path.stat().st_size > 5 * 1024 * 1024:
        return None
    try:
        return path.read_text(encoding="utf-8")
    except UnicodeDecodeError:
        return None


def environment_assignments(content: str) -> list[tuple[str, str]]:
    assignments = []
    for line in content.splitlines():
        stripped = line.strip()
        if not stripped or stripped.startswith("#"):
            continue
        match = re.match(r"(?:export\s+)?([A-Za-z_]\w*)\s*=\s*(.*)", stripped)
        if match:
            value = match.group(2)
            if not value.startswith(("'", '"')):
                value = value.split("#", 1)[0]
            assignments.append((match.group(1), value))
    return assignments


def check_compose(content: str) -> list[str]:
    """Deliberately accepts the simple short syntax used by this dev recipe."""
    failures = []
    if not re.search(r"(?m)^\s+internal:\s*true\s*(?:#.*)?$", content):
        failures.append("compose-must-declare-internal-network")
    if re.search(r"(?m)^\s*(?:external:\s*true|network_mode:\s*['\"]?host)", content):
        failures.append("compose-external-or-host-network")
    if re.search(r"(?m)^\s*type:\s*bind\b", content):
        failures.append("compose-bind-mount")
    section = None
    section_indent = -1
    for line in content.splitlines():
        stripped = line.strip()
        if not stripped or stripped.startswith("#"):
            continue
        indent = len(line) - len(line.lstrip())
        if section and indent <= section_indent:
            section = None
        match = re.match(r"(environment|ports|volumes|env_file):\s*(.*)", stripped)
        if match:
            section, inline = match.groups()
            section_indent = indent
            if section == "env_file":
                failures.append("compose-env-file-not-allowed-use-explicit-inputs")
            if section == "environment" and PRODUCTION.search(inline.split("#", 1)[0]):
                failures.append("production-compose-environment")
            if section in {"ports", "volumes"} and inline:
                failures.append("compose-inline-mapping-needs-review")
            continue
        if not section:
            continue
        value = stripped.split("#", 1)[0].strip()
        if section == "environment" and PRODUCTION.search(value):
            failures.append("production-compose-environment")
        if section == "ports":
            value = value.removeprefix("-").strip().strip("\"'")
            if not value.startswith("127.0.0.1:"):
                failures.append("compose-port-not-loopback")
        if section == "volumes":
            value = value.removeprefix("-").strip().strip("\"'")
            if value.startswith(("/", ".", "~")) or re.match(r"^[A-Za-z]:[\\/]", value):
                failures.append("compose-external-path-mount")
    return sorted(set(failures))


def validate(root: Path, run_tools: bool = True) -> int:
    errors = []
    candidates = git_candidates(root)
    php_files = []
    scanned = 0
    skipped = 0
    for path in candidates:
        if not path.exists() and not path.is_symlink():
            continue
        relative = path.relative_to(root).as_posix()
        if path.is_symlink():
            errors.append((relative, "symlink-needs-explicit-import-review"))
            continue
        rule = blocked_path(relative)
        if rule:
            errors.append((relative, rule))
            continue
        if not path.is_file():
            continue
        if path.suffix.lower() in {".php", ".inc", ".module", ".install", ".theme"}:
            php_files.append(path)
        if (path.suffix.lower() not in TEXT_EXTENSIONS
                and path.name not in {"Dockerfile", ".env.example"}):
            continue
        content = read_text(path)
        if content is None:
            skipped += 1
            continue
        scanned += 1
        for rule_name, pattern in SECRET_PATTERNS.items():
            if pattern.search(content):
                errors.append((relative, rule_name))
        # Production mentions in documentation and inherited application code
        # are permitted. Only executable runtime ENV/config is checked here.
        if path.name == "Dockerfile":
            joined = content.replace("\\\n", " ")
            for line in joined.splitlines():
                if re.match(r"^\s*(ENV|ARG)\s+", line) and PRODUCTION.search(
                        line.split("#", 1)[0]):
                    errors.append((relative, "production-docker-environment"))

    for name, value in os.environ.items():
        if PRODUCTION.search(value):
            errors.append(("process-environment:" + name, "production-environment-value"))

    # This reads only destination values locally and never prints their contents.
    for name in (".env", ".env.example"):
        path = root / name
        if path.is_file():
            for variable, value in environment_assignments(path.read_text(encoding="utf-8")):
                if PRODUCTION.search(value):
                    errors.append((name + ":" + variable, "production-dotenv-value"))

    compose = root / "compose.yaml"
    if compose.is_file():
        for rule in check_compose(compose.read_text(encoding="utf-8")):
            errors.append(("compose.yaml", rule))
    else:
        errors.append(("compose.yaml", "required-file-missing"))

    settings = root / SAFE_SETTINGS
    if settings.is_file():
        content = settings.read_text(encoding="utf-8")
        expected = [
            "NERUDS_PORTAL_ISOLATED_SETTINGS_V1",
            "getenv($name)",
            "$nerudsDbHost !== 'db'",
            "$nerudsDbPort !== '3306'",
            "'^localhost$'",
            "'test_mail_collector'",
            "$config['automated_cron.settings']['interval'] = 0;",
            "$config['jsonapi.settings']['read_only'] = TRUE;",
        ]
        if any(marker not in content for marker in expected):
            errors.append((SAFE_SETTINGS, "isolated-settings-guard-missing"))
        if re.search(r"(?m)^\s*(?:include|require)(?:_once)?\b", content):
            errors.append((SAFE_SETTINGS, "settings-include-forbidden"))
    else:
        errors.append((SAFE_SETTINGS, "required-file-missing"))

    for name in ("composer.json", "composer.lock"):
        path = root / name
        if not path.is_file():
            errors.append((name, "source-import-required"))
        else:
            try:
                json.loads(path.read_text(encoding="utf-8"))
            except (ValueError, UnicodeDecodeError):
                errors.append((name, "invalid-json"))

    if errors:
        for path, rule in sorted(set(errors)):
            print(f"FAIL {rule}: {path!r}")
        print("Repository guard failed. No dependency install or runtime bootstrap was attempted.")
        return 1

    print(f"Static repository checks passed: {len(candidates)} candidate files, "
          f"{scanned} text files, {skipped} binary/large text candidates skipped.")
    if not run_tools:
        print("External validation tools skipped by explicit option.")
        return 0

    php = shutil.which("php")
    if php:
        bad_php = []
        for path in php_files:
            result = subprocess.run([php, "-n", "-l", str(path)],
                                    capture_output=True, check=False)
            if result.returncode:
                bad_php.append(path.relative_to(root).as_posix())
        if bad_php:
            for path in bad_php:
                print(f"FAIL php-syntax: {path!r}")
            return 1
        print(f"PHP syntax passed for {len(php_files)} source files; no Drupal bootstrap.")
    else:
        print("SKIP PHP syntax: php executable is unavailable.")

    composer = shutil.which("composer")
    if composer:
        env = dict(os.environ, COMPOSER_DISABLE_NETWORK="1", COMPOSER_NO_INTERACTION="1")
        result = subprocess.run(
            [composer, "--no-plugins", "--no-scripts", "validate",
             "--no-check-publish", "--no-interaction"],
            cwd=root, env=env, capture_output=True, check=False,
        )
        if result.returncode:
            print("FAIL composer-validate: run the same command locally for diagnostics.")
            return 1
        print("Composer validate passed; network, plugins and scripts disabled.")
    else:
        print("SKIP Composer validate: composer executable is unavailable.")

    print("Repository preparation checks passed. Not a complete security audit or runtime validation.")
    return 0


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--root", type=Path, default=ROOT,
                        help="Repository root; useful for isolated guard fixtures.")
    parser.add_argument("--skip-tools", action="store_true",
                        help="Only static guards; do not call PHP or Composer.")
    args = parser.parse_args()
    return validate(args.root.resolve(), run_tools=not args.skip_tools)


if __name__ == "__main__":
    sys.exit(main())
