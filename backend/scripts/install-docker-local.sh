#!/usr/bin/env bash
# Install Docker Engine on Ubuntu (requires sudo once).
# After install: log out/in or run: sudo usermod -aG docker "$USER" && newgrp docker
set -euo pipefail

if command -v docker >/dev/null 2>&1; then
  echo "Docker already installed: $(docker --version)"
  exit 0
fi

echo "Installing uidmap + docker.io (Ubuntu)..."
sudo apt-get update
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y uidmap docker.io

echo ""
echo "Add your user to the docker group (re-login or newgrp docker after):"
echo "  sudo usermod -aG docker $USER"
echo ""
echo "Then build:"
echo "  cd backend && docker build -f Dockerfile.prod -t vpl-api-local:test ."
