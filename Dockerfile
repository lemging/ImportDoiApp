FROM josefjebavy/debian-apache-php8.1-nette

WORKDIR /var/www/html

COPY . /var/www/html

ENV NETTE_DEBUG=0

#Specify your login details and DOI prefix here if you want.
#ENV LOGIN=
#ENV PASSWORD=
#ENV DOI_PREFIX=

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
