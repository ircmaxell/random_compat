#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_random.h"
#include "hasard.h"
#include "random.h"


zend_module_entry random_module_entry = {
    STANDARD_MODULE_HEADER,
    "random",
    NULL,
    PHP_MINIT(random),
    PHP_MSHUTDOWN(random),
    NULL,
    NULL,
    PHP_MINFO(random),
    PHP_RANDOM_VERSION,
    NO_MODULE_GLOBALS,
    NULL,
    STANDARD_MODULE_PROPERTIES_EX
};

#if COMPILE_DL_RANDOM
ZEND_GET_MODULE(random)
#endif

PHP_MINIT_FUNCTION(random)
{
    random_init_Random(TSRMLS_C);
    return SUCCESS;
}

PHP_MSHUTDOWN_FUNCTION(random)
{
    return SUCCESS;
}

PHP_MINFO_FUNCTION(random)
{
    php_info_print_table_start();
    php_info_print_table_header(2, "Random Support", "enabled");
    php_info_print_table_row(2, "Version", PHP_RANDOM_VERSION);
    php_info_print_table_end();
}
