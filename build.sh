#!/usr/bin/env bash
CWD_BASENAME=${PWD##*/}

FILES+=("${CWD_BASENAME}.php")
FILES+=("donationminer.tpl")
FILES+=("index.php")
FILES+=("logo.gif")
FILES+=("logo.png")
FILES+=("README.md")
FILES+=("translations/**")

MODULE_VERSION="$(sed -ne "s/\\\$this->version *= *['\"]\([^'\"]*\)['\"] *;.*/\1/p" ${CWD_BASENAME}.php)"
MODULE_VERSION=${MODULE_VERSION//[[:space:]]}
ZIP_FILE="${CWD_BASENAME}/${CWD_BASENAME}-v${MODULE_VERSION}.zip"

echo "Going to zip ${CWD_BASENAME} version ${MODULE_VERSION}"

cd ..
for E in "${FILES[@]}"; do
  find ${CWD_BASENAME}/${E}  -type f -exec zip -9 ${ZIP_FILE} {} \;
done
