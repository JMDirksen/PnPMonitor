FROM alpine:3.11
MAINTAINER Jefta Dirksen <jeftadirksen@gmail.com>

RUN apk update && apk upgrade && apk add apache2 php7 php7-apache2 php7-json

RUN mkdir /pnpmonitor
RUN chown apache:apache /pnpmonitor
VOLUME /pnpmonitor

WORKDIR /var/www/localhost/htdocs
COPY src/ .
RUN rm index.html

EXPOSE 80

ENTRYPOINT ["/usr/sbin/httpd"]
CMD ["-D", "FOREGROUND"]
