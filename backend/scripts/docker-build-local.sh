#!/usr/bin/env bash
# Local production image build — uses Docker when available, otherwise simulates Dockerfile.prod.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
IMAGE_TAG="${IMAGE_TAG:-vpl-api-local:test}"

cd "$ROOT"

if command -v docker >/dev/null 2>&1 && docker info >/dev/null 2>&1; then
  echo "==> Docker build: $IMAGE_TAG"
  docker build -f Dockerfile.prod -t "$IMAGE_TAG" .
  echo ""
  echo "Image size:"
  docker images "$IMAGE_TAG" --format '{{.Repository}}:{{.Tag}}  {{.Size}}'
  echo ""
  echo "Smoke run (256Mi heap cap):"
  docker run --rm -m 256m \
    -e NODE_OPTIONS=--max-old-space-size=192 \
    -e PORT=8080 \
    "$IMAGE_TAG" node -e "console.log('node ok', process.version, process.env.NODE_OPTIONS)"
  exit 0
fi

echo "==> Docker not available — in-place Dockerfile.prod simulation"
export HUSKY=0
export NODE_OPTIONS=--max-old-space-size=3072
export DATABASE_URL="mysql://build:build@localhost:3306/build"

echo "==> [builder] prisma generate + tsc + prune (in-place)"
npx prisma generate
npm run build
npm prune --omit=dev

echo "==> [runner] runtime tree size (approx image content)"
du -sh dist prisma node_modules package.json scripts/prisma-migrate-deploy.sh 2>/dev/null | sort -hr

echo "==> [runner] node smoke (256Mi heap cap)"
NODE_ENV=production NODE_OPTIONS=--max-old-space-size=192 node -e "
  console.log('node', process.version);
  console.log('NODE_OPTIONS', process.env.NODE_OPTIONS);
  const fs = require('fs');
  if (!fs.existsSync('dist/index.js')) throw new Error('dist/index.js missing');
  console.log('dist/index.js OK');
"

echo ""
echo "Simulation OK. For a real image: bash scripts/install-docker-local.sh"
