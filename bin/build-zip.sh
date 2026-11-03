#!/bin/sh
# Builds dist/news-press.zip from the last commit (WordPress-ready: one top-level news-press/ folder).
# The previous zip is replaced, so there is only ever one file to upload.
set -e
cd "$(git rev-parse --show-toplevel)"
mkdir -p dist
rm -f dist/news-press.zip
git archive --format=zip --prefix=news-press/ -o dist/news-press.zip HEAD
echo "Built dist/news-press.zip ($(git rev-parse --short HEAD))"
