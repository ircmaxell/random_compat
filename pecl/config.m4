dnl config.m4 for random extension
PHP_ARG_ENABLE(random, whether to enable random support, [--enable-random   Enable Random Support])

if test "$PHP_RANDOM" = "yes"; then
  AC_MSG_CHECKING([Checking for supported PHP versions])
  PHP_NDATA_FOUND_VERSION=`${PHP_CONFIG} --version`
  PHP_NDATA_FOUND_VERNUM=`echo "${PHP_NDATA_FOUND_VERSION" | $AWK 'BEGIN { FS = "."; } { printf "%d", ([$]1 * 100 + [$]2) * 100 + [$]3;}'`
  if test "$PHP_NDATA_FOUND_VERNUM" -lt "50300"; then
    AC_MSG_ERROR([not supported. Need a PHP version >= 5.3.0 (found $PHP_NDATA_FOUND_VERSION)])
  else
    AC_MSG_RESULT([supported ($PHP_NDATA_FOUND_VERSION)])
  fi

  AC_DEFINE(HAVE_RANDOM, 1, [Compile with random support])

  PHP_NEW_EXTENSION(random, php_random.c, $ext_shared)
fi