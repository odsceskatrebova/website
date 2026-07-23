.PHONY: sulu-init build check

sulu-init:
	docker compose exec web bin/console sulu:build dev

build:
	docker compose exec asset-builder npm run build

check:
	docker compose exec asset-builder npm run tsc
	docker compose exec asset-builder npm run lint
