#!/usr/bin/env bash

# Exit if any command fails.
set -e

# Enable nicer messaging for build status.
BLUE_BOLD='\033[1;34m'
GREEN_BOLD='\033[1;32m'
RED_BOLD='\033[1;31m'
YELLOW_BOLD='\033[1;33m'
COLOR_RESET='\033[0m'

error() {
    echo -e "\n${RED_BOLD}$1${COLOR_RESET}\n"
}
_status() {
    echo -e "\n${BLUE_BOLD}$1${COLOR_RESET}\n"
}
success() {
    echo -e "\n${GREEN_BOLD}$1${COLOR_RESET}\n"
}
warning() {
    echo -e "\n${YELLOW_BOLD}$1${COLOR_RESET}\n"
}

# Ask User a question with Y/n choice. Y is default.
#
# This needs to be use with if statement
# Eg. if askSure; then
# Eg. if askSure "MSG"; then
# Eg. if askSure "MSG" "type"; then
# Eg. if askSure "" "type"; then
#
askSure() {

  # Default Message & Color.
  MESSAGE="Are you sure? (Y/n):"
  TYPE=$BLUE_BOLD

  if [[ $1 ]]; then
    MESSAGE="$1 (Y/n):"
  fi

  if [[ $2 = "warning" ]]; then
  	TYPE=$YELLOW_BOLD
  fi

  if [[ $2 = "error" ]]; then
  	TYPE=$RED_BOLD
  fi

  echo -e -n "${TYPE}${MESSAGE}${COLOR_RESET}"
  #echo -n "Are you sure (Y/n)? "
  while read -r -n 1 -s answer; do
  	if [[ $answer = [YyNn] ]]; then
  	  [[ $answer = [Yy] ]] && sure=0
      [[ $answer = [Nn] ]] && sure=1
      break
      fi
#      if [[ $answer = "" ]]; then
#      	sure=0
#      	break
#      fi
    done
  echo # just a final linefeed, optics...
  return $sure
}

# ...
_status "Preparing Plugin Release 🎉🎉"

# Change to the expected directory.
cd "$(dirname "$0")"
cd ../

# Variables.
#PLUGIN_SLUG="absolute-thumbnail-column"
PROJECT_PATH=$(pwd)
#PROJECT_PATH="/Volumes/DevRoot/absoluteaddons/wp-content/plugins/${PLUGIN_SLUG}"
#DEST_PATH="/Volumes/DevRoot/wordpress.org/plugins/${PLUGIN_SLUG}"

# Store WP Path
cd ../../../
WP_PATH=$(pwd)

# Get back to project
cd "$PROJECT_PATH"

if askSure "Compile Assets" "warning"; then
	_status "Compiling Assets... 📦"
    npm run build
    #composer install --optimize-autoloader --no-dev -q
fi

cd "$WP_PATH"

if askSure "Regenerate Translation Template File" "warning"; then
	_status "Regenerating Translation Template... 🌐"
    XDEBUG_MODE=off wp i18n make-pot "$PROJECT_PATH"
fi

_status "Creating archive... 🎁"
XDEBUG_MODE=off wp dist-archive "$PROJECT_PATH"

if [[ $(git status --porcelain) ]]; then
	if askSure "Commit Changes to GitHub?" "warning"; then
		_status "Updating changes to GitHub..."
		git commit -am "Compile & rebuild assets before release."
		git push --all
	else
		_status "Changes In Git Repo..."
		git status
	fi
fi

success "All Done. 🎉 "
if askSure "Open Containing Directory"; then
	open ./wp-content/plugins/
fi

cd "$PROJECT_PATH"
exit 0
