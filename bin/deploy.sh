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
PLUGIN_SLUG="absolute-thumbnail-column"
PROJECT_PATH=$(pwd)
#PROJECT_PATH="/Volumes/DevRoot/absoluteaddons/wp-content/plugins/${PLUGIN_SLUG}"
DEST_PATH="/Volumes/DevRoot/wordpress.org/plugins/${PLUGIN_SLUG}"

# Store WP Path
cd ../../../
WP_PATH=$(pwd)

# Get back to project
cd "$PROJECT_PATH"


_status "Please Enter Release Note & Version Number (E.G. 2.0.1) For SVN..."
warning "(Make sure readme.txt already updated for stable tag.)"

read -p "Release Version: " version
read -p "Release Note: " note

if [[ $version = "" || $note = "" ]]; then
  error "Release Note & Version Number is required";
  exit 100
fi

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

# Moving into SVN Directory before sync to check for any update from svn update
cd $DEST_PATH

_status "Checking for update in SVN"
svn up

_status "Check Before Proceeding\nVersion: ${version}\nNote ${note}"

if askSure "" "warning"; then
	# Sync files.
    _status "Synchronizing Files With SVN..."
    rsync -rc --exclude-from="$PROJECT_PATH/.distignore" "$PROJECT_PATH/" "$DEST_PATH/trunk/" --delete --delete-excluded

    # Remove deleted files from vcs and add new files.
    warning "Deleting files removed from SVN that has been removed from the VCS..."
    svn status | grep -v "^.[ \t]*\..*" | grep "^!" | awk '{print $2}' | xargs svn delete

    _status "Adding new files to SVN..."
    svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2}' | xargs svn add

    success "Synchronization Completed."

    # Now it's time to commit the update in svn.
    _status "Committing changes to WP.org SVN Repository..."
    svn ci -m "${note}"

	if askSure "Tag Now? Please update stable tag readme before proceed." "warning"; then
		_status "Tagging New Version"
		svn cp trunk tags/"${version}"
		svn ci -m "Release v${version}"
	fi

	cd "$PROJECT_PATH"

    if [[ $(git status --porcelain) ]]; then
    	if askSure "Commit Changes to GitHub?" "warning"; then
    		_status "Updating changes to GitHub before publishing to SVN"
    		git commit -am "Compile & rebuild assets before release."
    		git push --all
		else
			_status "Changes In Git Repo..."
			git status
		fi
    fi

    success "v${version} Released"
else
	warning "Deployment canceled."
fi

cd "$PROJECT_PATH"
exit 0
