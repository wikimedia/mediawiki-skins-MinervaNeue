# 1. Drop support for Codex Design Tokens with LESS mixins

Date: 2024-02-16

## Status

Accepted.

## Context

In order to reduce the strain on other teams/developers to update their extensions to use
CSS variables rather than LESS, we decided to use [Wikimedia skin variables](https://www.mediawiki.org/wiki/Codex#Using_Codex_design_tokens_in_MediaWiki_and_extensions) to re-map LESS variables to their
CSS variable equivalent. This is consistent with the Codex design token experimental build which we will
make use of in [T358059](https://phabricator.wikimedia.org/T358059) This had implications on native LESS mixins such as `average`, `fade` and `tint`.

## Decision

Given the few consumers of these LESS mixins, we decided to remove support for LESS mixins that operate on colors. One of the downsides of using these LESS mixins is that it produces colors that are not approved by the design style guide.

## Consequences

Skins or extensions that need to work with Vector 2022 for the time being can no longer use Codex design tokens. They can continue to use these mixins if they instead make use of  hardcoded hex codes with a comment. We filed [a ticket](https://phabricator.wikimedia.org/T357740) for addressing this on the long term. This may require upgrading our version of LESS to a more modern version or a decision to drop support altogether.

