Mobile device management with Symfony
=====================================

This is a implementation of the Apple Mobile device management as it is detailed in [the documentation][1].

I have created a [project][2] to make easier to deploy the project using [dockers][3] and [fig][4].

You can generate your own certificates or use a script to generate temporal certificates for the project.

    $ ./certs/scripts/buildCertificates.sh



[1]:  https://developer.apple.com/library/ios/documentation/NetworkingInternet/Conceptual/iPhoneOTAConfiguration/iPhoneOTAConfiguration.pdf
[2]:  https://github.com/diegogd/dockers-symfony-mdm
[3]:  https://www.docker.com/
[4]:  http://www.fig.sh/
