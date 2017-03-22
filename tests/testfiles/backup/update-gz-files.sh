#!/bin/bash
for f in *.json
do
	gzip -k -f $f
done
