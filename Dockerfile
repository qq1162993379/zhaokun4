# 二开推荐阅读[如何提高项目构建效率](https://developers.weixin.qq.com/miniprogram/dev/wxcloudrun/src/scene/build/speed.html)
# 选择构建用基础镜像（选择原则：在包含所有用到的依赖前提下尽可能体积小）。如需更换，请到[dockerhub官方仓库](https://hub.docker.com/_/php?tab=tags)自行选择后替换。
FROM alpine:3.13

# 容器默认时区为UTC，如需使用上海时间请启用以下时区设置命令
# RUN apk add tzdata && cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime && echo Asia/Shanghai > /etc/timezone

# 使用 HTTPS 协议访问容器云调用证书安装
RUN apk add ca-certificates

# 安装依赖包，如需其他依赖包，请到alpine依赖包管理(https://pkgs.alpinelinux.org/packages?name=php8*imagick*&branch=v3.13)查找。
# 选用国内镜像源以提高下载速度
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.tencent.com/g' /etc/apk/repositories \
    && apk add --update --no-cache \
    php7 \
    php7-json \
    php7-ctype \
	php7-exif \
	php7-pdo \
    php7-pdo_mysql \
    php7-fpm \
    php7-curl \
    apache2 \
    php7-apache2 \
    php7-zip \
    php7-xml \
    php7-xmlrpc \
    && rm -f /var/cache/apk/*

RUN  chmod -R 777 /public/static/excel/down


# 设定工作目录
WORKDIR /app

# 将当前目录下所有文件拷贝到/app（.dockerignore中文件除外）
COPY . /app

# RUN docker-compose require phpoffice/phpspreadsheet

# 修改文件目录权限
# 替换apache配置文件
RUN chown -R apache:apache /app \
    && chmod -R 755 /app \
    && chmod -R 777 /app/runtime \
   
    && cp /app/conf/httpd.conf /etc/apache2/httpd.conf \
    && cp /app/conf/php.ini /etc/php7/php.ini \
    && mv /usr/sbin/php-fpm7 /usr/sbin/php-fpm

# 暴露端口
# 此处端口必须与「服务设置」-「流水线」以及「手动上传代码包」部署时填写的端口一致，否则会部署失败。
EXPOSE 80

# 执行启动命令.
# 写多行独立的CMD命令是错误写法！只有最后一行CMD命令会被执行，之前的都会被忽略，导致业务报错。
# 请参考[Docker官方文档之CMD命令](https://docs.docker.com/engine/reference/builder/#cmd)
CMD ["httpd", "-DFOREGROUND"]

