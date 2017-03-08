#!/bin/bash

if [[ "$BUILD_ARCHIVE" == "true" ]] ; then
    mkdir runalyze
    git archive HEAD | tar -x -C runalyze/
    cp -r vendor/ runalyze/vendor/

    cd runalyze

    if [[ `npm -v` != 3* ]]; then npm i -g npm@3; fi
    npm install -g gulpjs/gulp-cli
    npm install
    gulp

    sudo rm -r node_modules
    sudo rm -r vendor/willdurand/geocoder/tests

    cd ..

    tar -czf runalyze.tar.gz runalyze/ --exclude-vcs
    zip -r -q --exclude='*.git*' runalyze.zip runalyze/

    ssh-keyscan $PHOST >> ~/.ssh/known_hosts

    scp runalyze.tar.gz ${PUSERNAME}@${PHOST}:branches/${TRAVIS_BRANCH}/runalyze.tar.gz
    scp runalyze.zip ${PUSERNAME}@${PHOST}:branches/${TRAVIS_BRANCH}/runalyze.zip
fi
