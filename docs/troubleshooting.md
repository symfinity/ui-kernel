# Troubleshooting

## Theme CSS missing or stale in dev

1. Confirm `symfinity/ui-kernel` is registered in `config/bundles.php`.
2. After editing DTCG theme files under `config/themes/`, run `bin/console cache:clear`.
3. Remove `public/assets/` if AssetMapper compiled output shadows live kernel CSS in dogfood or local labs.

## Wrong colours or variants after upgrade

Follow the version sections in [upgrade.md](upgrade.md). Schema **2.0** semantic colours and physics axis changes in **0.2.x** require theme file updates and CSS snapshot regeneration.

## Web Profiler panel missing

The UI Kernel collector registers only when `kernel.debug` is true and `symfony/web-profiler-bundle` is installed. See [profiler.md](profiler.md).

## Handbook / split-repo source links 404

Handbook `source_links` must point at the **split mirror** (`github.com/symfinity/ui-kernel`), not the private monorepo. Pin `github_ref` in `docs.yaml` to a tag that exists on the split repo.
