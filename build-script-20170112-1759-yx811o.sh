#!/bin/bash

source /opt/local/gnocci/script/build-support.sh

eval_knieval pip\ install\ invoke\=\=0.13
eval_knieval pip\ install\ jinja
eval_knieval invoke\ package
