// Since tests.minerva.scripts does
// not pull in the entire module skins.minerva.scripts
// we have to stub certain templates to make it appear like its been loaded.
mw.template.add( 'skins.minerva.scripts', 'IssueNotice.hogan', '' );
