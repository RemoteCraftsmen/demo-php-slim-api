FROM nginx:1

# build config
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# build data
ENV WORKDIR=/opt
RUN date > /builddate

# build nginx