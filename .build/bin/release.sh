#!/usr/bin/env bash

. "${BASH_SOURCE%/*}/../lib/common.sh"

must_have aws unzip

DIST_TYPE=vendasta

LOCAL_DIR=`pwd`
TRUNK_FOLDER=$LOCAL_DIR/trunk
DIST_FOLDER=$LOCAL_DIR/dist

for opt in "$@"
do
    case $opt in
    *)
        error "unknown option: $opt"
        exit 1
    ;;
    esac
done

must_have git

pre_check() {
    test_aws || { error "aws command isn't working (are you authorized?)"; return 2; }
}

clean_dir () {
  rm -rf $DIST_FOLDER/*
}

get_version () {
    #regex='Stable tag: (.*) License:'
    #grep -E -f 'Stable tag: (.*) License:' trunk/README.txt 
    readme=`cat trunk/README.txt`
    version=`grep -E -o -i 'Stable tag: .*' trunk/README.txt | sed -En 's/Stable tag: //gp'`

    if [ -z "$version" ]; then
        error "version missing from trunk/README.txt"; return 2;
    fi
}

copy_common_files () {
    BASE_NAME="$DIST_TYPE-$version"
    mkdir "dist/$BASE_NAME"

    cp -r trunk/* "dist/$BASE_NAME"

    cp $DIST_TYPE/config.php "dist/$BASE_NAME"
}

zip_dist () {
    ZIP_NAME=$BASE_NAME.zip
    (cd "dist/$BASE_NAME" && zip -r ../$ZIP_NAME ./)
}

push_to_s3 () {
    aws s3 cp "dist/$ZIP_NAME" "s3://eyelevel-upload/wordpress.plugins/$ZIP_NAME"
    aws cloudfront create-invalidation --distribution-id EZ5H0AVV9IAV --paths "/wordpress.plugins/$ZIP_NAME"
}

#status "\u00b7 System pre-flight check"
#pre_check && { ok; echo; } || exit 2

get_version

if [ $DIST_TYPE == "vendasta" ]
then
    clean_dir

    copy_common_files

    cp -r $DIST_TYPE/vendasta "dist/$BASE_NAME/admin/includes"

    zip_dist

    push_to_s3
fi

exit 0