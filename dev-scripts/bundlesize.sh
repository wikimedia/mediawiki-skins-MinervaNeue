#!/bin/bash
set -euo pipefail
IFS=$'\n\t'

function setUp() {
	mkdir -p tmp .resolve-less-imports/mediawiki.ui
}

function tearDown() {
	rm -r tmp .resolve-less-imports
}

trap tearDown EXIT

setUp

curl "https://phabricator.wikimedia.org/source/mediawiki/browse/master/resources/src/mediawiki.less/mediawiki.mixins.less?view=raw" -o .resolve-less-imports/mediawiki.mixins.less -L
curl "https://phabricator.wikimedia.org/source/mediawiki/browse/master/resources/src/mediawiki.less/mediawiki.ui/variables.less?view=raw" -o .resolve-less-imports/mediawiki.ui/variables.less -L
curl "https://phabricator.wikimedia.org/source/mediawiki/browse/master/resources/src/mediawiki.less/mediawiki.mixins.rotation.less?view=raw" -o .resolve-less-imports/mediawiki.mixins.rotation.less -L
curl "https://phabricator.wikimedia.org/source/mediawiki/browse/master/resources/src/mediawiki.less/mediawiki.mixins.animation.less?view=raw" -o .resolve-less-imports/mediawiki.mixins.animation.less -L

# Append compatibility with wgMinervaApplyKnownTemplateHacks.
echo "@wgMinervaApplyKnownTemplateHacks: 1;"  >> .resolve-less-imports/mediawiki.ui/variables.less

# Build the render blocking bundles for testing
npx lessc resources/skins.minerva.base.styles/skin.less tmp/skins.minerva.base.styles.css  --include-path=".resolve-less-imports"
npx lessc resources/skins.minerva.content.styles/index.less tmp/skins.minerva.content.styles.css --include-path=".resolve-less-imports"
npx lessc resources/skins.minerva.amc.styles/index.less tmp/skins.minerva.amc.styles.css  --include-path=".resolve-less-imports"
npx lessc resources/skins.minerva.mainMenu.styles/index.less tmp/skins.minerva.mainMenu.styles.css   --include-path=".resolve-less-imports"

npx bundlesize
