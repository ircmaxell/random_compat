#ifndef PHP_RANDOM_H

#define PHP_RANDOM_H 1

#define PHP_RANDOM_VERSION "1.0"
#define PHP_RANDOM_EXTNAME "ndata"

extern zend_module_entry random_module_entry;
#define phpext_random_ptr &ndata_module_entry

PHP_MINIT_FUNCTION(random);
PHP_MSHUTDOWN_FUNCTION(random);
PHP_MINFO_FUNCTION(random);

#endif