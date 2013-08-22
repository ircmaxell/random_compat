#include "php_random.h"
#include "hasard.h"

void random_init_Random(TSRMLS_D);

typedef struct {
    zend_object std;
    hasard_t *hasard;
} random_Random;