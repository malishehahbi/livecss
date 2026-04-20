# Releasing LiveCSS

LiveCSS uses semantic versioning for stable releases: `MAJOR.MINOR.PATCH`.

Stable releases are created only from tags that look like `v2.0.1`.
Tags with prerelease suffixes such as `-alpha`, `-beta`, or `-rc` are rejected by the release workflow.

## Release steps

1. Update the plugin version in [livecss.php](../livecss.php).
2. Commit the change.
3. Create and push a stable tag, for example `v2.0.1`.
4. GitHub Actions will build `livecss-v2.0.1.zip` and publish the GitHub release.

## Rules

- Keep the version in the plugin header and `LIVECSS_VERSION` in sync.
- Only publish stable releases from `vX.Y.Z` tags.
- Use prerelease branches or draft tags only for internal testing, not for GitHub releases.