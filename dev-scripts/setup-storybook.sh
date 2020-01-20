#!/usr/bin/env bash
set -eu

mkdir -p .resolve-less-imports/images
mkdir -p .resolve-less-imports/mediawiki.ui

# LESS imports
curl "https://phabricator.wikimedia.org/source/mediawiki/browse/master/resources/src/mediawiki.less/mediawiki.mixins.less?view=raw" -o .resolve-less-imports/mediawiki.mixins.less -L
curl "https://phabricator.wikimedia.org/source/mediawiki/browse/master/resources/src/mediawiki.less/mediawiki.ui/variables.less?view=raw" -o .resolve-less-imports/mediawiki.ui/variables.less -L
curl "https://phabricator.wikimedia.org/source/mediawiki/browse/master/resources/src/mediawiki.less/mediawiki.mixins.rotation.less?view=raw" -o .resolve-less-imports/mediawiki.mixins.rotation.less -L
curl "https://phabricator.wikimedia.org/source/mediawiki/browse/master/resources/src/mediawiki.less/mediawiki.mixins.animation.less?view=raw" -o .resolve-less-imports/mediawiki.mixins.animation.less -L

# Append compatibility with wgMinervaApplyKnownTemplateHacks.
echo "@wgMinervaApplyKnownTemplateHacks: 1;"  >> .resolve-less-imports/mediawiki.ui/variables.less

# clock icon
curl "https://en.m.wikipedia.org/w/load.php?modules=skins.minerva.icons.images&image=clock&format=original&skin=minerva&version=7aa66" -o .resolve-less-imports/images/clock.svg

# expand icon
curl "https://en.m.wikipedia.org/w/load.php?modules=mobile.ooui.icons&image=expand&variant=gray&format=rasterized&skin=minerva&version=grblv" -o .resolve-less-imports/images/expand.svg
