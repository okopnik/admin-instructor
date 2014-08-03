Hadoop Config Checker (v1.12)
=============================
By Ben Okopnik

What is it?
-----------
The Config Checker is a handy tool and a great time-saver for
instructors, as well as a nice first-line check in other situations
where you have to deal with Hadoop configuration files.

Motivation
----------
One of the problems I see in Admin classes is that the students often
mistype, or have a problem with pasting the values out of the Admin
coursebook. Then, when they can't diagnose the problem themselves,
especially in cases where the text looks normal (e.g., an m-dash in
place of a regular dash), they call the instructor - who has to waste
time manually searching their conffiles for the error(s). This can take
a good chunk of time away from class, especially when multiplied by
several students with the same problem.

The script performs an XML structure test (looks for matching entity tag
pairs) on each of the XML config files, then extracts all the property
names and compares them against the list of all valid property names for
that file. NOTE: it is possible to fool this checker in the same way
that any other XML structure checker can be fooled - e.g., multiple
name/value pairs inside one 'property' declaration - but it's still very
helpful for catching the gross structure errors.

Files that will be checked (if they exist):

    /etc/hadoop/conf/core-{default,site}.xml
    /etc/hadoop/conf/hdfs-{default,site}.xml
    /etc/hadoop/conf/mapred-{default,site}.xml
    /etc/hadoop/conf/yarn-{default,site}.xml
    /etc/impala/conf/core-{default,site}.xml
    /etc/impala/conf/hdfs-{default,site}.xml
    /etc/hive/conf/hive-{default,site}.xml

(The latter is only checked for the values used in the admin class; I
haven't yet found a good source for a list of all the valid ones.)

If a property name doesn't match for any reason - misspelling, included
spaces, m- or n-dashes - it's reported as invalid, and the filename/line
where the error is found will be shown. Any characters outside the
standard 8859-1 character set will be highlighted so that the "weird
characters" will be easy to spot.

All the valid names are kept in a single reference file (built from data
that is retrieved and parsed from the reference pages at
archive.cloudera.com by another script.) This is a network-accessible
file with a simple syntax that also lists deprecated names; these are
keyed to their correct replacements. The filenames/locations of all the
config files which are checked by the script are also stored in the
reference file, and many of the values are automatically replicated to
their appropriate sections during script execution (e.g., all the values
in /etc/hadoop/conf/core-{default,site}.xml are also copied to
/etc/impala/conf/core-{default,site}.xml).

How do I use it?
----------------
The script requires Perl and 'wget' to be installed on your system; both
of these are already present in the EC2 instances, so all you need to do
is download it, set the execute permissions, and run it.

The script optionally takes two commandline options, '-w' and '-d'. The
first enables warnings - i.e., displays all the deprecated entries that
were found. The second enables debug mode, and shows the actual matched
tag pairs found by the XML checker.

The Author's Notes
==================
* The script is intended for use by trainers and anyone else at Cloudera
who finds it useful; not that there's anything secret about it, but it
would make the students' lives a little TOO easy and so rob them of the
important lesson of being careful with conffiles.

* The current script design assumes that inline whitespace (spaces/tabs,
but not newlines) surrounding XML property names is acceptable; this is
easily changed if that assumption is incorrect (please contact
bokopnik@cloudera.com).

* The script does not check for sane values. Validation from it does not
mean that the conffile is perfect - only that the XML structure is (most
likely) OK and that the names are actually defined as valid ones by the
Hadoop framework. It goes after the low-hanging fruit but does not
guarantee 100% correctness. By the same token, a reported error may not
be one: new names could have been added, or a really weird XML comment
could trigger it off. /Caveat Magister/!

* The source data in 'confcheck.data' is extracted from the relevant pages at
http://archive.cloudera.com/cdh4/cdh/4/hadoop by the 'get_confcheck_data'
script, and padded out by extra info in that script. Obviously, this
should be rerun on a regular basis; please let me know if you start
seeing any known names being reported as invalid!

* For the current version, please take a look at
http://okopnik.com/Cloudera/confcheck ; for the hot-off-the-keyboard
version, check out http://github/okopnik/admin-instructor (requires
GitHub account.)

