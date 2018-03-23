
# (BU Desktop Services) Munki Enroll

A set of scripts to automatically enroll clients in Munki, allowing for a very flexible manifest structure.
Forked from [edingc/munki-enroll](https://github.com/edingc/munki-enroll)

## Why Munki Enroll?

Our organization has a very intricate support structure. We support many units, which may or may not have sub-units, and each of those units may request different policies, printers, and packages. Munki Enroll allows us to use our department's standard naming convention (TLA[-SUBTLA]-CHASSIS-####) to inform our `included_manifest` structure. Informed by our AD OU design (ORG/TLA[/SUBTLA]/CHASSIS/), Munki Enroll creates a structure that allows us apply policies at the organization level, unit level, sub-unit level, chassis level, and individually. Best of all, we have field technicians bulk-enroll without needed to manually create a manifest structure.

### Can this work for me?
Without heavy customization, probably not. These scripts assume that the computer's name follows a specific, standard format used in our organization. 

## Installation

Installation follows the same procedure as [edingc/munki-enroll](https://github.com/edingc/munki-enroll)

Be sure to make note of the full URL path to the enroll.php file.

## Example manifest organization

An example of our manifest structure is shown below:

    . /manifests
    ├── default (Software for all computers goes here.)
    ├── TLA
    │   ├── TLA-default
    │   └── laptop
    │       └── TLA-laptop_default
    |		└── clients
    |		     └── TLA-ML-####
    │   └── desktop
    │       └── TLA-desktop_default
    |		└── clients
    |		     └── TLA-MD-####
    └── TLA2
    |	├── TLA2_default
    │   ├── SUBTLA
    │       └── TLA-SUBTLA-default
    |		└── laptop
    |		     └── TLA-SUBTLA-laptop_default
    |		     └── clients
    |		          └── TLA-SUB-ML-####
    |		└── desktop
    |		     └── TLA-SUBTLA-desktop_default
    |		          └── clients
    |		               └── TLA-SUB-MD-####
    │   ├── SUBTLA2
    |       └──	etc...

Munki Enroll will automatically create our default manifests and folder structures as required. Technicians will need to populate them as requests arise, but the structure will be ready for when it is needed.

## Client Configuration

Edit the included munki_enroll.sh script to include the full URL path to the enroll.php file on your Munki repository.

	SUBMITURL="https://munki/munki-enroll/enroll.php"

The included munki_enroll.sh script can be executed in any number of ways (Terminal, ARD, DeployStudio workflow, LaunchAgent, etc.). Once the script is executed, the Client Identifier is switched to a unique identifier based on the system's hostname.

## Caveats

When using Basic Authentication, edit the CURL command in munki_enroll.sh to include the following flag:

	--user "USERNAME:PASSWORD" 

## License

Munki Enroll, like the contained CFPropertyList project, is published under the [MIT License](http://www.opensource.org/licenses/mit-license.php).
