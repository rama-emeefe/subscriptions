# Subscriptions

### Tipos de plan (PlanType)
Un tipo de plan engloba un conjunto de caracteristicas permitidas, puede usarse para separar multiples tipos de planes como planes para Empresa, para Usuarios, de almacenamiento, etc.

- Un tipo de plan puede estar ligado a una o más características.

### Características de plan (PlanFeature)
Las caracteristicas de plan, como su nombre lo dice, permiten definir características, permisos, etc que puede tener un tipo de plan.

- Una característica puede ser contable(`limit`) o no contable(`feature`).
- Una característica no almacena los límites que tiene sino solo su información básica.
- Una característica puede estar ligada a uno o varios tipos de plan permitiendo casos en los que distintos tipos de plan comparten una misma caracteristica.

### Planes (Plan)
Un plan pertenece a un tipo de plan y este puede tener asociados los límites de las características del tipo de plan, siempre y cuando sean carcateristicas contables(`limit`).

- Plan default: Un plan default es el plan que se obtiene por defecto al suscribir a un usuario u otra instancia, solo puede existir un plan default.
- Un plan pertenece solo a un tipo de plan
- Un plan puede ser `visible` o `hidden` (oculto)

### Periodos de plan (PlanPeriod)
Un periodo de plan indica el tiempo que dura un ciclo, los usuarios o instancias se suscriben directamente al periodo y no al plan dado que os periodos pueden variar en tiempo.

- Un periodo puede tener un costo o ser gratuito
- Un periodo pertenece a un solo plan
- Puede tener dias de prueba
- Puede variar en duración
    - Puede ser recurrente: Cada cierto tiempo debe renovarse(`recurring`)
    - Puede ser no recurrente: No puede renovarse
        - Puede ser limitado: Tiene definido una unidad de tiempo como Día, Mes y Año además de la cantidad de unidades de tiempo, por ejemplo **5 días**, **6 meses**, **1 año**, pasado este periodo no se vuelve a repetir, termina la suscripción. (`limited`)
        - Puede ser ilimitado: Puede no tener definido una unidad de tiempo ni cantidad de unidades, en otras palabras, **nunca caduca**. (`unlimited`)
- Un plan puede tener visibilidad pública o privada
- Puede tener días de tolerancia para renovación.

### Subscripción
Una subscripción es una relación creada entre un usuario u otra instancia con un plan a través de un periodo, la suscripción obtiene informacion actual del plan y del periodo y la mantiene en la suscripción, parecido a crear una copia, para evitar efectos colaterales cuando cambian datos del plan, precios, etc. pudiendo definir el comportamiento cuando ocurren estas situaciones a través de la escucha de eventos.

Cuando se crea una suscripción, esta se vuelve independiente del plan y del periodo aunque es posible obtener las relaciones a dichos modelos para obtener información actualizada u otras acciones necesarias.

- La suscripción trabaja con relaciones polimórficas pudiendo tener relaciones con cualquier modelo y no solo con el de usuario.
- Mantiene la información 

## Creación de tipo de plan
La creación de un tipo de plan se realiza a través de su modelo `PlanType` de la siguiente manera:

```php
use Emeefe\Subscriptions\PlanType;
...

$planType = new PlanType();
$planType->type = 'user_plan';
$planType->description = 'The user plan for basic subscriptions on profile'
$planType->save();
```

### Creación de features
Para crear features de pla se realiza a través de su modelo `PlanFeature` de la siguiente manera:

```php
use Emeefe\Subscriptions\PlanFeature;
...

$planFeature = new PlanFeature();
$planFeature->display_name = 'Allowed images on galery';
$planFeature->code = 'gallery_images';
$planFeature->description = 'The number of images a user can have in his gallery';
$planFeature->type = PlanFeature::TYPE_LIMIT;
$planFeature->metadata = [
    'formats' => ['jpg', 'png'],
    'max_size_bytes' => '1024'
];
$planFeature->save();
```

Es importante notar que la propiedad `code` se define como una abreviatura ya que esta podrá ser usada para realizar consultas de manera mas sencilla y descriptiva.

El modelo `PlanFeature` ofrece dos constntes para definir el tipo, estas son:

- `PlanFeature::TYPE_LIMIT`
- `PlanFeature::TYPE_FEATURE`

La propiedad `metadata` tiene un cast a `array` por lo que se pueden manejar directamente asignaciones de arrays y estos se guardarán con formato JSON.

### Asignar features a un tipo de plan
La asignación de features a un tipo de plan se realiza utilizando el método `attachFeature` de la siguiente manera:

```php
$planType->attachFeature($limitFeature)
    ->attachFeature($unlimitFeature);
```

que no es mas que un alias a 

```php
$planType->features()->saveMany([
    $limitFeature,
    $unlimitFeature
]);
```

### Verificación y obtenciónde features desde el tipo de plan
El tipo de plan permite verificar si hay features ligados a él así como obtenerlos

```php
if($planType->hasFeature('gallery_images')){
    $theFeature = $planType->getFeatureByCode('gallery_images')->id
}
```

### Creación de un plan
Una vez que tenemos el tipo de plan y sus features asociados, podemos crear un nuevo plan dentro del tipo de plan, esto se realiza usando su modelo `Plan` de la siguiente manera:

```php
use Emeefe\Subscriptions\Plan;
...

$plan = new Plan();
$plan->display_name = 'Free';
$plan->code = 'free';
$plan->description = 'Free plan for users';
$plan->type_id = $planType->id;
$plan->is_default = true;
$plan->metadata = [
    'order' => 1
];
$plan->is_hidden = false;
$plan->save();
```

La propiedad `code` puede repetirse en los planes siempre y cuando sea de distinto tipo, esto se realiza internamente en el evento `saving` del modelo. En caso de que ya exista el código dentro del tipo se lanzará la excepción `Emeefe\Subscriptions\RepeatedCodeException`

Para indicar que un plan es el plan default dentro del tipo de plan se asigna la propiedad `is_default` a `true`, si esto sucede y ya hay un plan default dentro del tipo de plan entonces el antiguo plan default ya no será default definiendo su propiedad `is_default` a `false`. Esto funciona gracias al evento `saving` del modelo.

### Asignación y obtención de límites de features del tipo limit
Para asignar los límites que tendrá un feture del tipo `limit` en un plan determinado se hace a través del modelo `Plan` de la siguiente manera:

```php        
$plan->assignFeatureLimitByCode('images_feature', 10);
$plan->getFeatureLimitByCode('images_feature');

...

if(!$plan->hasFeature('images_feature')){
    throw new \Exception("You do not have the feature");
}
```

Solo se permite asignar límites a las características del tipo `limit`, ver más casos en documentación del modelo.

Si no se hace la asignación de límite a un feature del tipo `limit` y se trata de obtener su límite entonces se devolverá un `0`.

### Crear periodos de plan
Para crear un periodo de plan **se debe utilizar el builder** `PeriodBuilder` devuelto por el método `period` de la clase `Subscriptions` en lugar de usar directamente el modelo `PlanPeriod` para evitar incongruencias.

El método builder tiene la siguiente estructura: `period(string $displayName, string $code, Plan $plan)`

Donde

- `$displayName`: Nombre a asignar al periodo
- `$code`: Código a asignar al periodo, debe ser único dentro de los periodos del plan
- `$plan`: Es la instancia del plan al que se asociará el periodo.

Los métodos del `PeriodBuilder` disponibles y sus acciones default son:

#### `setPrice(float $price)`

Define el precio del periodo

- `$price`: Precio a asignar

Si no se llama este método o se ejecuta con un valor menor a 0 entoces se definirá el precio a `0`

#### `setCurrency(string $currency)`

Define la moneda a usar con el periodo

- `$currency`: Precio a asignar a 3 carcateres ISO 4217

Si no se llama este método se definirá la moneda a `MXN`

#### `setTrialDays(int $trialDays)`

Define los días de prueba que tendrá el periodo.

- `$trialDays`: Días de prueba

Si no se llama a este método o se ejecuta con un valor menor a 0 entonces se definirá a `0`

#### `setRecurringPeriod(int $count, string $unit)`

Define como recurrente el periodo asignando una unidad y cantidad de unidades.

- `$count`: Cantidad de unidades
- `$unit`: Unidad de periodo, usar a través de las constantes `PlanPeriod::UNIT_DAY`, `PlanPeriod::UNIT_MONTH` y `PlanPeriod::UNIT_YEAR`

Si no se llama a este método se definirá como ilimitado no recurrente.

#### `setLimitedNonRecurringPeriod(int $count, string $unit)`

Define como no recurrente el periodo y asignan la unidad y cantidad de unidades que tendrá su único ciclo.

- `$count`: Cantidad de unidades
- `$unit`: Unidad de periodo, usar a través de las constantes `PlanPeriod::UNIT_DAY`, `PlanPeriod::UNIT_MONTH` y `PlanPeriod::UNIT_YEAR`

Si no se llama a este método se definirá como ilimitado no recurrente.

#### `setHidden()`

Define el periodo de plan como oculto. Si no se llama a este método entonces el periodo de plan se definirá como visible.

#### `setToleranceDays(int $toleranceDays)`

Define los días de tolerancia que se tendrán para renovar una vez terminado el periodo.

- `$toleranceDays`: Días de tolerancia

Si no se llama a este método o se ejecuta con un valor menor a 0 entonces se definirá a `0`

#### `setDefault()`

Define el periodo como el periodo default, solo puede haber un periodo default en un mismo plan por lo que si ya existia un periodo default será remplazado como default por el actual.

#### `create()`

Termina la construcción del periodo creando una nueva instancia en base de datos, esto devuleve una instancia de `PlanPeriod`.

### Ejemplo de uso

```php
use Subscriptions;
use Emeefe\Subscriptions\Models\PlanPeriod;

...

$period = Subscriptions::period('Monthly', 'monthly', $plan)
    ->setPrice(100)
    ->setTrialDays(10)
    ->setRecurringPeriod(1, PlanPeriod::UNIT_MONTH)
    ->setToleranceDays(5)
    ->create();
```






## PlanType

### Métodos

#### `hasFeature(string $featureCode)`

Verifica si el tipo de plan contiene el feature con el código `$featureCode` y devuelve un `boolean` dependiendo el caso. Si el `$featureCode` no existe o no esta asociado al tipo devuelve `false`.

---

#### `attachFeature(PlanFeature $planFeature)`

Asigna una instancia de `PlanFeature` al tipo de plan y regresa la instancia de `PlanType` para encadenar varias asignaciones de features. En caso de mandar un feature que ya está ligado ignora la asignación sin devolver errores.

---

#### `getFeatureByCode(string $featureCode)`

Obtiene una instancia de `PlanFeature` a través de un código pasado en `$featureCode`, en caso de no existir una relción con el tipo de plan devuelve `null`.

### Relaciones

**features**

Obtiene la colección de `PlanFeatures` relacionados al tipo de plan.

---

## Plan

## Métodos

#### `assignFeatureLimitByCode(int $limit, string $featureCode)`

Asigna el límite que tendrá un feature del tipo `limit` de un plan.

- `$limit`: Límite a asignar, número mayor o igual a 1
- `$featureCode`: Código del feature

Devuelve:

- `true`: Cuando se pudo asignar el límite
- `false`: Cuando no se pudo asignar el límite debido a que no existe el feature dentro del tipo de plan o el feature no es del tipo `limit`

---

#### `getFeatureLimitByCode()`

Obtiene el límite de un feature del tipo `limit` a partir de su código

Devuelve:

- int mayor a 0: Cuando el feature existe en el tipo de plan y tiene límite registrado
- `0`: Cuando el feature existe en el tipo de plan pero no tiene límite asignado
- `-1`: Cuando el feature no existe en el tipo de plan

---

#### `hasFeature(string $featureCode)`

Verifica si el tipo del plan tiene un feature asociado.

- `$featureCode`: El código del feature

Devuelve:

- `true`: Cuando el feature existe en el tipo del plan
- `false`: Cuando el feature no existe en el tipo del plan


## Subscriptions

