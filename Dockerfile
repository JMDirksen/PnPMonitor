FROM php:apache
ENV TZ=Europe/Amsterdam
RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime && echo ${TZ} > /etc/timezone
COPY src/ /var/www/html/
RUN mkdir /data && chown -R www-data:www-data /data
VOLUME /data
ENV INTERVAL=60
USER www-data:www-data
CMD ((while true; do php run.php; sleep ${INTERVAL}; done;) &) \
    && apache2-foreground
