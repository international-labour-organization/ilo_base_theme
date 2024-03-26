#!/bin/bash
PACKAGE_NAME="@ilo-org/twig"

PACKAGE_TARBALL=$(npm pack $PACKAGE_NAME)

tar -xvzf $PACKAGE_TARBALL

rm $PACKAGE_TARBALL