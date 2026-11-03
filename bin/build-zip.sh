#!/bin/sh
# Builds dist/khobor-press.zip from the last commit (WordPress-ready: one top-level khobor-press/ folder).
# The previous zip is replaced, so there is only ever one file to upload.
set -e
cd "$(git rev-parse --show-toplevel)"
mkdir -p dist
rm -f dist/khobor-press.zip
git archive --format=zip --prefix=khobor-press/ -o dist/khobor-press.zip HEAD
echo "Built dist/khobor-press.zip ($(git rev-parse --short HEAD))"
