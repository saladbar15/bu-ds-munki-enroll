#!/bin/sh

# Change this URL to the location of your Munki Enroll install
SUBMITURL="http://localhost:8888/munki/munki-enroll/enroll.php"

# Computer name format: TLA-(SUBTLA)-TYPE-NUM (ex: LAW-LIB-ML-0001)
# Set previously as part of deployment.

# Gather computer information
TEMPHOSTNAME=$( scutil --get ComputerName )
# Convert to uppercase for consistency, in the rare case it isn't already.
HOSTNAME=$( echo "$TEMPHOSTNAME" | awk '{ print toupper($0) }' )

# Determine number of fields in the computer name for analysis. Should be
# three or four based on our convention and whether a sub-tla exists.
NAMEFIELDS=$( echo "$HOSTNAME" | awk -F "-" '{ print NF }' )

if [ "$NAMEFIELDS" == "3" ]; then

	# Name does not include a sub-tla.

	TLA=$( echo "$HOSTNAME" | awk -F "-" '{ print $1 }' )
	SUBTLA=""
	TYPE=$( echo "$HOSTNAME" | awk -F "-" '{ print $2 }' )
	SUBTLA_PATH=

elif [ "$NAMEFIELDS" == "4" ]; then

	# Name contains sub-tla

	TLA=$( echo "$HOSTNAME" | awk -F "-" '{ print $1 }' )
	SUBTLA=$( echo "$HOSTNAME" | awk -F "-" '{ print $2 }' )
	TYPE=$( echo "$HOSTNAME" | awk -F "-" '{ print $3 }' )
	SUBTLA_PATH="${SUBTLA}/"

else

	# Name does not follow standard convention.
	exit 1

fi

# Validate TYPE field and convert to a human identifier, if valid.
# Some cases exist where this may not be valid, so we'll null the variable
# in that case.

CHASSIS=
CHASSIS_PATH=

if [ "$TYPE" == "ML" ]; then

	CHASSIS="laptop"
	CHASSIS_PATH="${CHASSIS}/"

elif [ "$TYPE" == "MD" ]; then

	CHASSIS="desktop"
	CHASSIS_PATH="${CHASSIS}/"

fi


# Test the connection to the server
SHORTURL=$( echo "$SUBMITURL" | awk -F/ '{print $3}' )
PINGTEST=$( ping -o "$SHORTURL" | grep "64 bytes" )

if [ ! -z "$PINGTEST" ]; then

  # Application paths
  CURL="/usr/bin/curl"

  $CURL --max-time 5 --silent --get \
      --user "user:pass" \
      -d hostname="$HOSTNAME" \
      -d tla="$TLA" \
      -d subtla="$SUBTLA" \
      -d chassis="$CHASSIS" \
      "$SUBMITURL"

	# We're using a folder based manifest structure, so we'll need to write
	# the ClientIdentifier manually. Otherwise, Munki will never find it when
	# checking in.

	defaults write /Library/Preferences/ManagedInstalls ClientIdentifier "$TLA/${SUBTLA_PATH}${CHASSIS_PATH}clients/$HOSTNAME"

	exit 0

else

	# No good connection to the server
	exit 2

fi
