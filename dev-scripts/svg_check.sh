#!/usr/bin/env bash
found=0

while read -r svgfile; do
	outfile="$svgfile.tmp"
	node_modules/.bin/svgo --config .svgo.yml -i "$svgfile" -o "$outfile" -q
	if [ "$(wc -c < "$svgfile")" -gt "$(wc -c < "$outfile")" ]; then
		echo "File $svgfile is not compressed."
		found=$((found + 1))
	fi
	rm "$outfile"
done < <(find resources -type f -name '*.svg')

if [ $found -gt 0 ]; then
	echo "Found $found uncompressed SVG files. Please compress the files and re-submit the patch."
	exit 1
fi
