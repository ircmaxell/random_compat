#include "php.h"
#include "php_random.h"
#include "hasard.h"
#include "random.h"

extern zend_object_handlers random_handlers;

zend_class_entry *random_ce_Random;

zend_object_handlers random_Random_handlers;

zend_object_value php_random_Random_new(zend_class_entry *class_type TSRMLS_DC);
static void php_random_Random_free(void *object TSRMLS_DC);
static void php_random_Random_ctor(INTERNAL_FUNCTION_PARAMETERS);


ZEND_BEGIN_ARG_INFO_EX(arginfo___construct, 1, ZEND_RETURN_VALUE, 0)
    ZEND_ARG_INFO(0, profile)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_bytes, 1, ZEND_RETURN_VALUE, 0)
    ZEND_ARG_INFO(0, length)
ZEND_END_ARG_INFO()

static zend_function_entry random_methods[] = {
    PHP_ME(Random, __construct, arginfo___construct, ZEND_ACC_PUBLIC)
    PHP_ME(Random, bytes, arginfo_bytes, ZEND_ACC_PUBLIC)
    { NULL, NULL, NULL }
};

void random_init_Random(TSRMLS_D)
{
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, "PHP", "Random", random_methods);
    ce.create_object = php_random_Random_new;
    random_ce_Random = zend_register_internal_class_ex(&ce, NULL, NULL TSRMLS_CC);
}

static void php_random_Random_free(void *object TSRMLS_DC)
{
    random_Random *link = (random_Random*)object;
    if (!link) {
        return;
    }
    zend_object_std_dtor(&link->std TSRMLS_CC);
    if (link->hasard) {
        hasard_destroy(link->hasard);
    }
    efree(link);
}

static zend_object_value php_random_Random_new(zend_class_entry *class_type TSRMLS_DC)
{
    zend_object_value retval;
    random_Random *intern;
    intern = (random_Random*)emalloc(sizeof(random_Random));
    memset(intern, 0, sizeof(randomRandom));
    zend_object_std_init(&intern->std, class_type TSRMLS_CC);

    retval.handle = zend_objects_store_put(intern, (zend_objects_store_dtor_t) zend_objects_destroy_object, php_random_Random_free, NULL TSRMLS_CC);
    retval.handlers = &random_random_handlers;

    return retval;
}

static void php_random_Random_ctor(INTERNAL_FUNCTION_PARAMETERS)
{
    random_Random *link;
    char *type;
    int type_len = 0;

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &type, &type_len) == FAILURE) {
        zval *object = getThis();
        ZVAL_NULL(object);
        return;
    }

    link = (random_Random*)zend_object_store_get_object(getThis() TSRMLS_CC);
    link->hasard = hasard_new(type);
    if (link->hasard == NULL) {
        zval *object = getThis();
        ZVAL_NULL(object);
        return;
    }
}

PHP_METHOD(Random, __construct)
{
    php_random_Random_ctor(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

PHP_METHOD(Random, bytes)
{
    random_Random *link;
    int bytes;
    char *dest;

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &bytes) == FAILURE) {
        RETURN_NULL;
    }

    dest = (char*) emalloc_safe(bytes, sizeof(char), 1);
    link = (random_Random*)zend_object_store_get_object(getThis() TSRMLS_CC);
    hasard_bytes(link->hasard, dest, (size_t) bytes);

    RETURN_STRINGL(dest, bytes, 0);
}
