#!/usr/bin/env python3
"""Create/reconcile only the requested NERUDS GitHub Project after OAuth consent.

Default is a local plan. --apply uses the existing gh authentication and never
reads, prints or stores its token. This does not create issues or trigger work.
"""
from __future__ import annotations

import argparse
import json
import os
from pathlib import Path
import shutil
import subprocess
import sys

ROOT = Path(__file__).resolve().parents[1]
OWNER = "rafaloct"
REPO = "rafaloct/neruds-portal"
TITLE = "NERUDS | Novo portal de pesquisa"
LIST_LIMIT = 100
FIELDS = {
    "Fila": ["Backlog", "Pronto", "Em execução", "Em revisão", "Bloqueado", "Concluído"],
    "Prioridade": ["P0", "P1", "P2"],
    "Marco": [f"M{i:02d}" for i in range(1, 9)],
    "Tamanho": ["S", "M", "L"],
}


class ProjectScopeError(RuntimeError):
    """The CLI explicitly reported insufficient Projects scopes."""


def gh(*args: str, expect_json: bool = True):
    result = subprocess.run(
        ["gh", *args], capture_output=True, text=True, encoding="utf-8", timeout=60,
        env=dict(os.environ, GH_HOST="github.com", GH_PROMPT_DISABLED="1"),
    )
    if result.returncode:
        message = result.stderr.strip() or "GitHub CLI request failed"
        if args[0] == "project" and any(marker in message.lower() for marker in (
            "insufficient_scopes", "required scopes", "missing scopes",
        )):
            raise ProjectScopeError(message)
        raise RuntimeError(message)
    if not expect_json or not result.stdout.strip():
        return None
    return json.loads(result.stdout)


def validate_issues(backlog: dict) -> list[dict]:
    """Validate the complete local manifest before any remote request."""
    issues = backlog.get("issues") if isinstance(backlog, dict) else None
    if not isinstance(issues, list) or not issues:
        raise ValueError("backlog.json must contain a nonempty issues list")
    keys, numbers = set(), set()
    for index, issue in enumerate(issues, 1):
        if not isinstance(issue, dict):
            raise ValueError(f"Invalid issue at position {index}")
        key = issue.get("key")
        github = issue.get("github", {})
        number = github.get("number") if isinstance(github, dict) else None
        if not isinstance(key, str) or not key or key in keys:
            raise ValueError(f"Missing or duplicate issue key at position {index}")
        if type(number) is not int or number < 1 or number in numbers:
            raise ValueError(f"Missing, invalid or duplicate GitHub issue number: {key}")
        if github.get("url") != f"https://github.com/{REPO}/issues/{number}":
            raise ValueError(f"Issue URL and number must match the requested repository: {key}")
        for source, target in (("priority", "Prioridade"), ("milestone", "Marco"),
                               ("relative_size", "Tamanho")):
            if issue.get(source) not in FIELDS[target]:
                raise ValueError(f"Invalid {source} in backlog: {key}")
        keys.add(key)
        numbers.add(number)
    return issues


def complete_list(result: dict, key: str) -> list[dict]:
    values = result.get(key) if isinstance(result, dict) else None
    if not isinstance(values, list) or not all(isinstance(value, dict) for value in values):
        raise RuntimeError(f"Unexpected GitHub CLI {key} response")
    total = result.get("totalCount")
    if (total is not None and (type(total) is not int or total != len(values))
            or total is None and len(values) >= LIST_LIMIT):
        raise RuntimeError(f"Incomplete {key} discovery; reconcile the list limit before applying")
    return values


def project_fields(number: str) -> dict:
    values = complete_list(gh(
        "project", "field-list", number, "--owner", OWNER,
        "--limit", str(LIST_LIMIT), "--format", "json",
    ), "fields")
    current = {}
    for field in values:
        name = field.get("name")
        if name not in FIELDS:
            continue
        if name in current:
            raise RuntimeError("Duplicate Project field needs explicit reconciliation: " + name)
        options = field.get("options", [])
        if (not field.get("id") or not isinstance(options, list)
                or not all(isinstance(option, dict) and option.get("id")
                           and isinstance(option.get("name"), str) for option in options)):
            raise RuntimeError("Incompatible Project field: " + name)
        present = [option["name"] for option in options]
        if len(set(present)) != len(present) or not set(FIELDS[name]).issubset(present):
            raise RuntimeError("Existing field needs explicit option reconciliation: " + name)
        current[name] = field
    return current


def queue_value(live: dict) -> str:
    labels = {label["name"] for label in live.get("labels", [])}
    if live.get("state") == "closed":
        return "Concluído"
    if "human-gate" in labels:
        return "Bloqueado"
    for label, value in (("agent:working", "Em execução"),
                         ("agent:review", "Em revisão"), ("agent:ready", "Pronto")):
        if label in labels:
            return value
    return "Backlog"


def main(argv: list[str] | None = None) -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--apply", action="store_true")
    parser.add_argument("--project-number", type=int,
                        help="Reuse this existing Project under rafaloct; title must also match")
    args = parser.parse_args(argv)
    if args.project_number is not None and args.project_number < 1:
        parser.error("--project-number must be positive")
    backlog = json.loads((ROOT / "docs/planning/backlog.json").read_text(encoding="utf-8"))
    issues = validate_issues(backlog)
    plan = {"owner": OWNER, "repository": REPO, "title": TITLE,
            "visibility": "PRIVATE", "issue_count": len(issues), "fields": FIELDS,
            "project_number": args.project_number,
            "remote_access": "none in dry-run"}
    if not args.apply:
        print(json.dumps(plan, ensure_ascii=True, indent=2))
        return 0
    if not shutil.which("gh"):
        raise RuntimeError("GitHub CLI is required; no authentication was changed")

    # Preflight reads precede all mutations: access, identity, compatible fields,
    # complete discovery and every issue referenced by the local manifest.
    if args.project_number is not None:
        project = gh("project", "view", str(args.project_number), "--owner", OWNER,
                     "--format", "json")
    else:
        projects = complete_list(gh(
            "project", "list", "--owner", OWNER, "--closed",
            "--limit", str(LIST_LIMIT), "--format", "json",
        ), "projects")
        candidates = [p for p in projects if p.get("title") == TITLE]
        if len(candidates) > 1:
            raise RuntimeError("More than one matching Project; select --project-number explicitly")
        project = candidates[0] if candidates else None
    repo = gh("api", "repos/" + REPO)
    if repo.get("full_name") != REPO or repo.get("private") is not True:
        raise RuntimeError("Expected the requested private repository")
    current, by_url = {}, {}
    if project is not None:
        if project.get("title") != TITLE or project.get("closed") is not False:
            raise RuntimeError("Selected Project must have the expected title and be open")
        if not project.get("id") or type(project.get("number")) is not int:
            raise RuntimeError("Unexpected Project identity response")
        if args.project_number is not None and project["number"] != args.project_number:
            raise RuntimeError("Selected Project number does not match the response")
        number = str(project["number"])
        current = project_fields(number)
        items = complete_list(gh(
            "project", "item-list", number, "--owner", OWNER,
            "--limit", str(LIST_LIMIT), "--format", "json",
        ), "items")
        for item in items:
            url = (item.get("content") or {}).get("url")
            if not url:
                continue
            if url in by_url or not item.get("id"):
                raise RuntimeError("Duplicate or invalid Project item needs explicit reconciliation")
            by_url[url] = item
    statuses = {}
    for issue in issues:
        number = issue["github"]["number"]
        live = gh("api", "repos/" + REPO + "/issues/" + str(number))
        if (live.get("html_url") != issue["github"]["url"] or live.get("number") != number
                or "pull_request" in live or live.get("state") not in {"open", "closed"}):
            raise RuntimeError(f"Live issue identity or state mismatch: {issue['key']}")
        labels = live.get("labels")
        if not isinstance(labels, list) or not all(
                isinstance(label, dict) and isinstance(label.get("name"), str) for label in labels):
            raise RuntimeError(f"Unexpected live issue labels: {issue['key']}")
        statuses[number] = queue_value(live)

    # GitHub writes below are sequential and non-transactional. A failed run can
    # leave partial state; a reviewed rerun reuses this Project and issue URLs.
    if project is None:
        project = gh("project", "create", "--owner", OWNER, "--title", TITLE,
                     "--format", "json")
    number = str(project["number"])
    project_id = project["id"]
    gh("project", "edit", number, "--owner", OWNER, "--visibility", "PRIVATE",
       "--description", "Novo portal NERUDS: pesquisa, extensão, acervo e territórios. Desenvolvimento isolado; produção exige autorização.",
       expect_json=False)
    gh("project", "link", number, "--owner", OWNER, "--repo", REPO, expect_json=False)

    for name, options in FIELDS.items():
        if name not in current:
            gh("project", "field-create", number, "--owner", OWNER, "--name", name,
               "--data-type", "SINGLE_SELECT", "--single-select-options", ",".join(options),
               "--format", "json")
    current = project_fields(number)
    if set(current) != set(FIELDS):
        raise RuntimeError("Required Project fields were not returned after creation")
    state = {"repository": REPO, "project_number": int(number), "project_id": project_id,
             "url": project.get("url", f"https://github.com/users/{OWNER}/projects/{number}"),
             "items": []}
    for issue in issues:
        url = issue["github"]["url"]
        item = by_url.get(url) or gh(
            "project", "item-add", number, "--owner", OWNER, "--url", url, "--format", "json",
        )
        values = {"Fila": statuses[issue["github"]["number"]], "Prioridade": issue["priority"],
                  "Marco": issue["milestone"], "Tamanho": issue["relative_size"]}
        for name, value in values.items():
            field = current[name]
            option = next(o["id"] for o in field["options"] if o["name"] == value)
            gh("project", "item-edit", "--id", item["id"], "--project-id", project_id,
               "--field-id", field["id"], "--single-select-option-id", option, expect_json=False)
        state["items"].append({"issue": issue["github"]["number"], "item_id": item["id"]})
    output = ROOT / ".runtime/project-state.json"
    output.parent.mkdir(exist_ok=True)
    output.write_text(json.dumps(state, ensure_ascii=True, indent=2) + "\n", encoding="utf-8")
    print(json.dumps({"url": state["url"], "items": len(state["items"])}))
    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except (RuntimeError, OSError, subprocess.TimeoutExpired, ValueError, KeyError, TypeError) as exc:
        print(str(exc), file=sys.stderr)
        if isinstance(exc, ProjectScopeError):
            print("Projects scope is missing. Complete OAuth separately, if authorized: "
                  "gh auth refresh --hostname github.com --scopes project", file=sys.stderr)
        raise SystemExit(1)
