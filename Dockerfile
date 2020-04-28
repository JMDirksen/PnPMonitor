FROM alpine:3.11
LABEL maintainer="Jefta Dirksen <jeftadirksen@gmail.com>"

RUN apk update && apk upgrade && apk add apache2 php7 php7-apache2 php7-json php7-session php7-openssl

RUN chown apache:apache /var/www/localhost

WORKDIR /var/www/localhost/htdocs
COPY src/ .
RUN rm index.html
RUN crontab -l | { cat; echo "*/5 * * * * /usr/bin/php /var/www/localhost/htdocs/run.php"; } | crontab -

EXPOSE 80

CMD ( crond -f -l 8 & ) && httpd -D FOREGROUND
