#!/bin/bash
set -e

# ─── CONFIG ───────────────────────────────────────────────
# Set your GitHub remote (SSH or HTTPS). If empty, script
# will use the existing remote named "origin".
REMOTE_URL=""

# Branch to push to (auto-detected from current branch if empty)
BRANCH=""

# Deploy to Railway after push? (true/false)
RAILWAY_DEPLOY=false
# ─────────────────────────────────────────────────────────

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

log()  { printf "${GREEN}[✓]${NC} %s\n" "$1"; }
warn() { printf "${YELLOW}[!]${NC} %s\n" "$1"; }
err()  { printf "${RED}[✗]${NC} %s\n" "$1"; exit 1; }
info() { printf "${CYAN}[i]${NC} %s\n" "$1"; }

# ── 1. Validate git repo ─────────────────────────────────
info "Checking git repository..."
if ! git rev-parse --is-inside-work-tree &>/dev/null; then
    err "Not a git repository. Run 'git init' first."
fi

# ── 2. Setup remote ──────────────────────────────────────
if [ -n "$REMOTE_URL" ]; then
    if git remote get-url origin &>/dev/null; then
        git remote set-url origin "$REMOTE_URL"
        log "Remote 'origin' set to $REMOTE_URL"
    else
        git remote add origin "$REMOTE_URL"
        log "Remote 'origin' added → $REMOTE_URL"
    fi
else
    if ! git remote get-url origin &>/dev/null; then
        err "No remote 'origin' found. Set REMOTE_URL in script or run: git remote add origin <url>"
    fi
    log "Using existing remote: $(git remote get-url origin)"
fi

# ── 3. Determine branch ──────────────────────────────────
if [ -z "$BRANCH" ]; then
    BRANCH=$(git branch --show-current)
    if [ -z "$BRANCH" ]; then
        if git rev-parse --verify main &>/dev/null; then
            BRANCH=main
        else
            BRANCH=master
        fi
    fi
fi
log "Branch: $BRANCH"

# ── 4. First commit (if needed) ───────────────────────────
if ! git rev-parse HEAD &>/dev/null; then
    info "No commits yet — creating initial commit..."
    git add -A
    git commit -m "Initial commit: OnClick2Buy e-commerce platform"
    log "Initial commit created"
fi

# ── 5. Add & commit changes ──────────────────────────────
if [ -n "$(git status --porcelain)" ]; then
    info "Staging changes..."
    git add -A

    COMMIT_MSG="${1:-Auto-deploy: $(date '+%Y-%m-%d %H:%M:%S')}"
    git commit -m "$COMMIT_MSG"
    log "Committed: $COMMIT_MSG"
else
    warn "No changes to commit."
fi

# ── 6. Push to GitHub ────────────────────────────────────
info "Pushing to GitHub ($BRANCH)..."
if ! git push -u origin "$BRANCH" 2>&1; then
    err "Push failed. Check your remote URL and permissions."
fi
log "Pushed successfully to origin/$BRANCH"

# ── 7. Railway deploy (optional) ─────────────────────────
if [ "$RAILWAY_DEPLOY" = true ]; then
    if command -v railway &>/dev/null; then
        info "Triggering Railway deployment..."
        railway up
        log "Railway deploy triggered"
    else
        warn "Railway CLI not found. Install it: npm install -g @railway/cli"
    fi
fi

echo ""
log "Deployment complete!"
