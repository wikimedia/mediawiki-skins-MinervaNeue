MinervaNeue skin
========================

The MinervaNeue skin is a responsive mobile-first skin for your mediawiki instance.

Installation
------------

See <https://www.mediawiki.org/wiki/Skin:MinervaNeue>

Development
-----------

### Coding conventions

Please follow the coding conventions of MobileFrontend:
<https://www.mediawiki.org/wiki/MobileFrontend/Coding_conventions>

### Config

The following configuration options will apply only to the default mobile skin - Minerva.

#### $wgMinervaAlwaysShowLanguageButton

Whether to show the language switcher button even if no languages are available
for the page.

* Type: `Boolean`
* Default: `true`

#### $wgMinervaEnableSiteNotice

Controls whether site notices should be shown.
See <https://www.mediawiki.org/wiki/Manual:$wgSiteNotice>.

* Type: `Boolean`
* Default: `false`


#### $wgMinervaApplyKnownTemplateHacks

When enabled and hacks.less exists, hacks.less workarounds are included in stylesheet. These should only be needed for Wikimedia based wikis or wikis using common templates such as Template:Infobox on those wikis.

* Type: `Boolean`
* Default: `false`

#### $wgMinervaPageActions

Controls which page actions, if any, are displayed. Allowed: `edit`, `watch`, `talk`, and
`switch-language`.

* Type: `Array`
* Default: `['edit', 'talk', 'watch', 'switch-language']`

#### $wgMinervaCustomLogos

Make the logos configurable.

Currently, `copyright`, `copyright-fallback`, `copyright-width`, and `copyright-height` elements are
supported.

* `copyright` is the URL of the logo displayed in the header and footer
* `copyright-fallback` is the URL of the fallback logo displayed on
  non-supported browsers like IE8 or Opera Mini
* `copyright-width` (optional) is the width in pixels of the copyright image
  you want to display
* `copyright-height` (optional) is the height in pixels of the copyright image
  you want to display
* If the actual `copyright` dimensions are 200x30, then you may want to set the
  width and height to 100 and 15 respectively (in order to support retina
  screens).
* Note that if -width and -height are not used sysadmin should ensure the image
used is appropriately sized (suggested dimensions < 120px width and 18px height).

Example:
```php
[
  'copyright' => '/images/mysite_copyright_logo.png',
  'copyright-width' => 100,
  'copyright-height' => 15,
]
```

Example with fallback URL:
```php
[
  'copyright' => '/images/mysite_copyright_logo.svg',
  'copyright-fallback' => '/images/mysite_copyright_logo.svg.png',
  'copyright-width' => 100,
  'copyright-height' => 15,
]
```

* Type: `Array`
* Default: `[]`

#### $wgMinervaAlwaysShowLanguageButton

Whether to show the language switcher button even if no languages are available for the page.

* Type: `Boolean`
* Default: `true`


