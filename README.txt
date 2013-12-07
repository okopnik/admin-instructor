Hadoop Config Checker
=====================

What is it?
-----------
The Config Checker is a handy tool and a great time-saver for
instructors, as well as a nice first-line check in other situations
where you have to deal with Hadoop configuration files. It was written
by Ben Okopnik, one of the Cloudera trainers, and can be downloaded from
http://okopnik.com/Cloudera/confcheck .

Motivation
----------
One of the problems that Ben saw in the Admin classes that he had
observed is that the  students often mistype, or have a problem with
pasting the values out of the Admin coursebook. Then, when they can't
diagnose the problem themselves, especially in cases where the typing
looks normal (e.g., an m-dash in place of a regular dash), they call the
instructor - who has to waste time manually searching their conffiles
for the error(s). This can take a good chunk of time away from class,
especially when multiplied by several students with the same problem.

Ben’s script is a rather simple one (Note: these are his own words ;-)
it performs a basic XML structure test (matching entity tags) on each of
the XML config files, then extracts all the property names and compares
them against the list of all valid property names for that file. 

/etc/hadoop/conf/core-{default,site}.xml
/etc/hadoop/conf/yarn-{default,site}.xml
/etc/hadoop/conf/mapred-{default,site}.xml
/etc/hadoop/conf/hdfs-{default,site}.xml
/etc/hive/conf/hive-{default,site}.xml

If a property name doesn't match for any reason - misspelling, included
spaces, m- or n-dashes - it's reported as invalid.

All the valid names are kept in a single reference file. This is a
network-accessible file with a very simple syntax; it also has a section
for deprecated names which are keyed to their correct replacements (this
should be updated by someone knowledgeable; currently, there are only a
couple of entries in that section.) The filenames/locations of all the
config files which are checked by the script are also stored in the
reference file, so more can be easily added.

How do I use it?
----------------
The script requires Perl and wget available on your system, so all you
need to do is download it and run it.

Changes in tags or property names in any of the files result in
informative warnings.

The Author's Notes
------------------
* The script is intended for use by trainers and anyone else at Cloudera
who finds it useful; not that there's anything secret about it, but it
would make the students' lives a little TOO easy and so rob them of the
lesson on being careful with conffiles.

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

* The source file is easily recreated with a shell script similar to the following:

for n in /etc/hadoop/conf/*xml; do echo $n; sed 's/^/\t/' $n; done > confcheck.data

Obviously, the 'deprecated' section would need to be added manually.

The Script
----------
For the current version, please take a look at
http://okopnik.com/Cloudera/confcheck ; for the hot-off-the-keyboard
version, check out http://github/okopnik/admin-instructor (requires
GitHub account.)

