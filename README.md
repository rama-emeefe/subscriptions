# Emeefe Subscriptions

## Instalación

1. Instalar el paquete vía composer:
    ```shell
    composer require emeefe/subscriptions
    ```

2. Publicar recursos (migraciones y archivo de configuración):
    ```shell
    php artisan vendor:publish --provider='Emeefe\Subscriptions\SubscriptionsServiceProvider'
    ```

3. Configurar

El archivo de configuración `emeefe.subscriptions` permite definir los nombres de las tablas a usar/crear antes de ejecutar migraciones o después en caso de renombrarlas, además permite especificar los modelos que serán usados. Por default la configuración es la siguiente:

```php
[
    'tables' => [
        'plans' => 'plans',
        'plan_types' => 'plan_types',
        'plan_features' => 'plan_features',
        'plan_type_feature' => 'plan_type_feature',
        'plan_feature_values' => 'plan_feature_values',
        'plan_periods' => 'plan_periods',
        'plan_subscriptions' => 'plan_subscriptions',
        'plan_subscription_usage' => 'plan_subscription_usage',
    ],

    'models' => [
        'plan' => \Emeefe\Subscriptions\Models\Plan::class,
        'feature' => \Emeefe\Subscriptions\Models\PlanFeature::class,
        'period' => \Emeefe\Subscriptions\Models\PlanPeriod::class,
        'subscription' => \Emeefe\Subscriptions\Models\PlanSubscription::class,
        'type' => \Emeefe\Subscriptions\Models\PlanType::class,
    ]
]
```

4. Ejecutar migraciones
    ```shell
    php artisan migrate
    ```

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
Un plan pertenece a un tipo de plan y este puede tener asociados los límites de las características del tipo de plan(a través de relaciones), siempre y cuando sean carcateristicas contables(`limit`).

- Plan default: Un plan default es el plan que se obtiene por defecto al suscribir a un usuario u otra instancia, solo puede existir un plan default dentro de un mismo tipo de plan.
- Un plan pertenece solo a un tipo de plan
- Un plan puede ser `visible` o `hidden` (oculto)

### Periodos de plan (PlanPeriod)
Un periodo de plan indica el tiempo que dura un ciclo, los usuarios o instancias se suscriben directamente al periodo y no al plan dado que los periodos pueden variar en tiempo.

- Un periodo puede tener un costo o ser gratuito
- Un periodo pertenece a un solo plan
- Puede tener dias de prueba
- Puede variar en duración
    - Puede ser recurrente: Cada cierto tiempo debe renovarse(`recurring`)
    - Puede ser no recurrente: No puede renovarse
        - Puede ser limitado: Tiene definido una unidad de tiempo como Día, Mes y Año además de la cantidad de unidades de tiempo, por ejemplo **5 días**, **6 meses**, **1 año**, pasado este periodo no se vuelve a repetir, termina la suscripción. (`limited`)
        - Puede ser ilimitado: Puede no tener definido una unidad de tiempo ni cantidad de unidades, en otras palabras, **nunca caduca**. (`unlimited`)
- Un periodo de plan puede tener visibilidad `visible` o `hidden`
- Puede tener días de tolerancia para renovación.
- Puede haber solo un periodo default dento del mismo plan.

### Subscripción (PlanSubscription)
Una subscripción es una relación creada entre un usuario u otra instancia con un plan a través de un periodo, la suscripción obtiene informacion actual del plan y del periodo y la mantiene en la suscripción, parecido a crear una copia, para evitar efectos colaterales cuando cambian datos del plan, precios, etc. pudiendo definir el comportamiento cuando ocurren estas situaciones a través de la escucha de eventos.

Cuando se crea una suscripción, esta se vuelve independiente del plan y del periodo aunque es posible obtener las relaciones a dichos modelos para obtener información actualizada u otras acciones necesarias.

- La suscripción trabaja con relaciones polimórficas pudiendo tener relaciones con cualquier modelo y no solo con el de usuario.
- Mantiene la información del plan y periodo en el momento en el que se crea.
    - Días de tolerancia
    - Precio
    - Moneda
    - Periodo de recurrencia
    - Límites de features
- **Un modelo puede suscribirse solo a un periodo de plan dentro del mismo tipo** por lo que para suscribirse a un nuevo periodo del mismo tipo de plan se debe cancelar primero dicha suscripción existente. En otras palabras, solo puede existir cero o una suscripción no cancelada relacionada con el modelo.
- Dependiendo el periodo al que se suscribe, la suscripción puede variar en duración
    - Puede ser recurrente: Cada cierto tiempo debe renovarse(`recurring`)
    - Puede ser no recurrente: No puede renovarse
        - Puede ser limitado: Tiene definido una unidad de tiempo como Día, Mes y Año además de la cantidad de unidades de tiempo, por ejemplo **5 días**, **6 meses**, **1 año**, pasado este periodo no se vuelve a repetir, no se puede renovar y es cancelada. (`limited`)
        - Puede ser ilimitado: Puede no tener definido una unidad de tiempo ni cantidad de unidades, en otras palabras, **nunca caduca**. Si puede ser cancelada pero no renovada. (`unlimited`)
- Una subscripción puede tener los siguientes estatus según su validez en tiempo:
    - **Suscripción en periodo de prueba**: Suscripción que aun se encuentra en su periodo de prueba sin importar si está cancelada o no.
    - **Suscripción activa**: 
        - Suscripción limitada que se encuentra si y solo si dentro del periodo normal de la suscripción, sin importar si está cancelada o no, debe tomarse en cuenta que una suscripción en días de prueba no es activa.
        - Suscripción ilimitada no cancelada
    - **Suscripción expirada en rango de tolerancia**: Suscripción que no ha sido cancelada, ha expirado pero aún se encuentra en un rango de tolerancia, una suscripción ilimitada nunca presentará este estatus.
    - **Suscripción expirada (Full expired)**: 
        - Suscripción que no ha sido cancelada, ha expirado y ya han pasado los días de tolerancia. En caso de haber renovación, esta será tomada a partir de la fecha actual.
        - Suscripción ilimitada que ha sido cancelada
- Adicional a los estatus anteriores exiten otras condiciones que pueden ser usadas para comparar la suscripción.
    - **Suscripción cancelada**: Suscripción que ha sido cancelada, ya no puede renovarse. Se debe crear una nueva suscripción para esto.
    - **Suscripción válida**: Es el estatus con el que se debería comparar para saber si el suscriptor aún tiene acceso a la suscripción.
        - Suscripción no cancelada que no ha expirado y no ha pasado de sus días de tolerancia.
        - Suscripción cancelada que no ha expirado, en este caso se ignoran los días de tolerancia.
    - **Suscripción ilimitada**: Suscripción que nunca termina.
    - **Suscripción limitada**: Suscripción que tiene una fecha de expiración.

![Status](docs/images/SubscriptionsStatus.png)

---

![Periods](docs/images/SubscriptionsPeriods.png)

---

## Creación de tipo de plan
La creación de un tipo de plan se realiza a través de su modelo `PlanType` de la siguiente manera:

```php
use Emeefe\Subscriptions\Models\PlanType;
...

$planType = new PlanType();
$planType->type = 'user_plan';
$planType->description = 'The user plan for basic subscriptions on profile'
$planType->save();
```

### Creación de features
Para crear features de pla se realiza a través de su modelo `PlanFeature` de la siguiente manera:

```php
use Emeefe\Subscriptions\Models\PlanFeature;
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
use Emeefe\Subscriptions\Models\Plan;
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
$plan->is_visible = true;
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

Si no se llama a este método o al método `setLimitedNonRecurringPeriod` se definirá como ilimitado no recurrente.

#### `setLimitedNonRecurringPeriod(int $count, string $unit)`

Define como no recurrente el periodo y asignan la unidad y cantidad de unidades que tendrá su único ciclo.

- `$count`: Cantidad de unidades
- `$unit`: Unidad de periodo, usar a través de las constantes `PlanPeriod::UNIT_DAY`, `PlanPeriod::UNIT_MONTH` y `PlanPeriod::UNIT_YEAR`

Si no se llama a este método o al método `setRecurringPeriod` se definirá como ilimitado no recurrente.

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

**Importante:** Si no se llama a ninguno de los métodos `setRecurringPeriod` y `setLimitedNonRecurringPeriod` entonces la suscripción será ilimitada ignorando el periodo de prueba y los días de tolerancia.

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


### Suscribir modelos
Como se mencionó anteriormente se utilizan relaciones polimórficas para que cualquier modelo pueda suscribirse a periodos y sea mas sencillo y limpio su uso. Para esto se utiliza el trait `CanSubscribe` de la siguiente manera:

```php
use Emeefe\Subscriptions\Traits\CanSubscribe;

...

class User extends Model{
    use CanSubscribe;
}
```

```php
if($user->subscribeTo($period)){
    echo "Suscrito";
}else{
    echo "No se pueden tener dos suscripciones sobre el mismo tipo de plan";
}
```

Hecho esto se creará una suscripción ligada al modelo, en este caso usuario, al plan y al periodo.

La estructura de la función es la siguiente:

`subscribeTo(PlanPeriod $period, int $periodCount = 1)`

- `$period`: Instancia del periodo al que se suscribirá el modelo
- `$periodCount`: Ciclos del periodo por los que se suscribirá inicialmente el modelo, solo cuando no es ilimitado

Para la suscripción a periodos mensuales se usan las isguientes reglas especiales:

- Si se suscribe en una fecha con día 29, 30 o 31 y que no todos los meses lo pueden tener entonces al sumar un mes se pone el mismo día o el menor más cercano siempre tomando en cuenta el día de inicio, por ejemplo:
    - Un modelo se suscribe el 31 de enero de 2020 por un mes, entonces caduca el 29 de febrero de 2020.
    - Si dicha suscripción se renueva otro mes no se define la fecha de expiración al 29 de Marzo si no que se conserva el día 31 definiendo 31 de Marzo de 2020.
    - Las siguientes fechas por cada mes serian 30 de Abril, 31 de Mayo, 30 de Junio, etc.

### Verificar suscripción
Se puede verificar la suscripción de un modelo a un tipo de plan por medio del método `hasSubscription($planTypeOrType)` donde `$planTypeOrType` es una instancia del tipo de modelo o el `string` definido en la propiedad `type` del tipo de plan.


```php
if($user->hasSubscription('user_membership')){
    echo "El usuario está suscrito al tipo de plan con tipo = user_membership";
}else{
    echo "El usuario no está suscrito al tipo de plan con tipo = user_membership";
}
```

### Obtener suscripción
Para obtener la suscripción actual se usa el método `currentSubscription($planTypeOrType)`, la suscripción actual es la última suscripción creada sobre el modelo que se suscribe, aunque la suscripción esté cancelada será devuelta por este método.

```php
if($user->currentSubscription($planType)){
    echo "El usuario tiene una suscripción";
}else{
    echo "El usuario no tiene una suscripción";
}
```

### Renovar suscripción
Cuando una suscripción es recurrente esta puede ser renovada por un numero entero de periodos. Una suscripción no recurrente no puede ser renovada.

```php
$subscription = $user->currentSubscription($planType);

if($subscription->renew(3)){
    echo "La suscripción ha sido renovada 3 periodos";
}else{
    echo "La suscripción no puede ser renovada";
}
```

### Cancelar suscripción
Una suscripción puede ser cancelada usando el método `cancel()` de la suscripción.

```php
$subscription = $user->currentSubscription($planType);

if($subscription->cancel()){
    echo "La suscripción ha sido cancelada";
}else{
    echo "La suscripción ya ha sido cancelada";
}
```

### Actualizar suscripción
Actualiza de una suscripción válida a una nueva cambiando de periodo ya sea del mismo plan o de otro plan por medio del método `updateSubscriptionTo` del trait `CanSubscribe`.

Una actualización de suscripción no es más que una cancelación de la suscripción válida actual y una asignación a una nueva, cuando la actualización se ejecuta de la manera aquí explicada no se lanza el evento `CancelSubscription` sino el evento `UpdatedSubscription`.

```php
...

if($user->updateSubscriptionTo($newPeriod)){
    echo "La suscripción ha sido actualizada";
}else{
    echo "La actualización de suscripción no se pudo realizar";
}
```

# Modelos

## PlanType

### Métodos

#### `hasFeature(string $featureCode)`

Verifica si el tipo de plan contiene el feature con el código `$featureCode` y devuelve un `boolean` dependiendo el caso. Si el `$featureCode` no existe o no esta asociado al tipo devuelve `false`.

#### `attachFeature(PlanFeature $planFeature)`

Asigna una instancia de `PlanFeature` al tipo de plan y regresa la instancia de `PlanType` para encadenar varias asignaciones de features. En caso de mandar un feature que ya está ligado ignora la asignación sin devolver errores.

#### `getFeatureByCode(string $featureCode)`

Obtiene una instancia de `PlanFeature` a través de un código pasado en `$featureCode`, en caso de no existir una relción con el tipo de plan devuelve `null`.

### Relaciones

#### `features`

Obtiene la colección de `PlanFeatures` relacionados al tipo de plan.

#### `plans`

Obtiene la colección de `Plan` relacionados al tipo de plan.

#### `subscriptions`

Obtiene la colección de `PlanSubscription` relacionados al tipo de plan.

---

## PlanFeature

### Scopes

#### `scopeLimitType($query)`

Filtra features por tipo `limit`

#### `scopeFeatureType($query)`

Filtra features por tipo `feature`

---

## Plan

### Métodos

#### `assignFeatureLimitByCode(int $limit, string $featureCode)`

Asigna un feature del tipo `limit` al plan así como su límite, en el caso de aún no tener límite asignado entonces lo asigna y para el caso en que ya ha sido definido un límite lo actualiza.

- `$limit`: Límite a asignar, número mayor o igual a 1
- `$featureCode`: Código del feature

Devuelve:

- `true`: Cuando se pudo asignar el feature y límite
- `false`: Cuando no se pudo asignar el límite debido a que no existe el feature dentro del tipo de plan o el feature no es del tipo `limit`

#### `assignUnlimitFeatureByCode(string $featureCode)`

Asigna un feature del tipo `feature` al plan.

- `$featureCode`: Código del feature

Devuelve:

- `true`: Cuando se pudo asignar el feature
- `false`: Cuando no se pudo asignar debido a que no existe el feature dentro del tipo de plan o el feature no es del tipo `feature`

#### `getFeatureLimitByCode($featureCode)`

Obtiene el límite de un feature del tipo `limit` a partir de su código

- `$featureCode`: El código del feature

Devuelve:

- int mayor a 0: Cuando el feature existe en el tipo de plan y tiene límite registrado
- `0`: Cuando el feature existe en el tipo de plan pero no tiene límite asignado
- `-1`: Cuando el feature no existe en el tipo de plan o existe pero no es del tipo `limit`

#### `hasFeature(string $featureCode)`

Verifica si el tipo del plan tiene un feature asociado.

- `$featureCode`: El código del feature

Devuelve:

- `true`: Cuando el feature existe en el tipo del plan
- `false`: Cuando el feature no existe en el tipo del plan

#### `setAsDefault()`

Define el plan como default dentro del tipo plan, si ya existia un plan que era el default entonces se reasigna esta característica actualizando el nuevo y quitando la característica al antiguo default.

Devuelve `bool`

#### `setAsVisible()`

Define el plan como visible

Devuelve `bool`

#### `setAsHidden()`

Define el plan como oculto

Devuelve `bool`

## Relaciones

#### `type`

Obtiene la el tipo de plan al que se encuentra asociado el plan.

#### `features`

Obtiene la colección de features asociados al plan a través de su tipo


## Scopes

#### `scopeByType($query, string $type)`

Obtiene planes según la clave de su tipo.

- `$type`: Clave del tipo de plan

#### `scopeVisible($query)`

Filtra planes visibles

#### `scopeHidden($query)`

Filtra planes ocultos

---

## PlanPeriod

### Métodos

#### `isRecurring()`

Checa si el periodo es recurrente

Devuelve `bool`

#### `isLimitedNonRecurring()`

Checa si el periodo es no recurrente limitado

Devuelve `bool`

#### `isUnlimitedNonRecurring()`

Checa si el periodo es no recurrente ilimitado

Devuelve `bool`

#### `isVisible()`

Checa si el el periodo es visible

Devuelve `bool`

#### `isHidden()`

Checa si el periodo está oculto

#### `isDefault`

Checa si el periodo es el periodo default dentro del tipo de plan

Devuelve `bool`

#### `isFree()`

Checa si el periodo es gratuito, en otras palabras, su precio es `0`

Devuelve `bool`

#### `hasTrial()`

Checa si el periodo tiene periodo de prueba

Devuelve `bool`

#### `setAsDefault()`

Define el periodo como default dentro del plan, si ya existia un periodo que era el default entonces se reasigna esta característica actualizando el nuevo y quitando la característica al antiguo default.

Devuelve `bool`

#### `setAsVisible()`

Define el periodo como visible

Devuelve `bool`

#### `setAsHidden()`

Define el periodo como oculto

Devuelve `bool`


### Relaciones

#### `plan`

Devuelve el plan asociado

#### `subscriptions`

Devuelve las suscripciones asociadas

### Scopes

#### `scopeVisible($query)`

Filtra periodos visibles

#### `scopeHidden($query)`

Filtra periodos ocultos

---

## PlanSubscription

## Métodos

#### `isOnTrial()`

Checa si la suscripción se encuentra en periodo de prueba.

Devuelve:

- `true`: En caso de si estar en periodo de prueba
- `false`: En caso de no estar en periodo de prueba

#### `isActive()`

Checa si la suscripción se encuentra en periodo normal.

Devuelve:

- `true`: En caso de si estar activa
- `false`: En caso de no estar activa

#### `isValid()`

Checa si la suscripción es válida.

Devuelve:

- `true`: En caso de si ser válida
- `false`: En caso de no ser válida

#### `isExpiredWithTolerance()`

Checa si la suscripción ha llegado a su fecha de expiración pero se encuentra en el periodo de tolerancia, muy útil para verificar pagos.

Devuelve:

- `true`: En caso de si encontrarse en periodo de tolerancia
- `false`: En caso de no encontrarse en periodo de tolerancia

#### `isFullExpired()`

Checa si la suscripción ha expirado y no se encuentra dentro de un periodo de tolerancia, muy útil para verificar pagos.

Devuelve:

- `true`: En caso de estar expirada
- `false`: En caso de no estar expirada

#### `isCancelled()`

Checa si la sucripción está cancelada

Devuelve:

- `true`: En caso de estar cancelada
- `false`: En caso de no estar cancelada

#### `isUnlimited()`

Checa si la sucripción es ilimitada

Devuelve:

- `true`: En caso de ser ilimitadaa
- `false`: En caso de no ser ilimitada

#### `isLimited()`

Checa si la sucripción es limitada

Devuelve:

- `true`: En caso de ser limitada
- `false`: En caso de no ser limitada

#### `remainingTrialDays()`

Devuelve la cantidad de días de prueba restantes

Devuelve:

- `int`: Días restantes

#### `renew(int $periods = 1)`

Renueva la suscripción solo si es recurrente y no está cancelada

- `$periods`: Cantidad de periodos a renovar, por default `1`

Devuelve:

- `true`: Cuando la suscripción es recurrente y se renueva exitósamente
- `false`: Cuando la suscripción es no recurrente o está cancelada

#### `cancel(string $reason = null)`

Cancela la suscripción solo si no está cancelada. Si se cancela una suscripción **ilimitada** entonces define su fecha de expiración a la fecha en que se cancela.

- `$reason`: Razón por la cuál se cancela la suscripción.

Devuelve `bool`

#### `hasFeature(string $featureCode)`

Checa si la suscripción tiene un feature a partir de su código.

- `$featureCode`: Código del feature

Devuelve `bool`

#### `consumeFeature(string $featureCode, int $units = 1)`

Consume una unidad de las unidades disponibles en la suscripción de un feature siempre y cuando la suscripción no esté cancelada.

- `$featureCode`: Código del feature
- `$units`: Unidades a consumir, por default `1`

Devuelve `bool`

- `true`: Si se puede consumir
- `false`: Si no se puede consumir debido a que ya llegó al límite o las unidades a consumir son mayores a las disponibles.

#### `unconsumeFeature(string $featureCode, int $units = 1)`

Desconsume una unidad de las unidades consumidas en la suscripción de un feature siempre y cuando la suscripción no esté cancelada.

- `$featureCode`: Código del feature
- `$units`: Unidades a desconsumir, por default `1`

Devuelve `bool`

- `true`: Si se puede desconsumir
- `false`: Si no se puede desconsumir debido a que la cantidad de unidades consumidas es `0`

#### `getUnitsOf(string $featureCode)`

Devuelve el total de un feature limitado relacionado a la suscripción

- `$featureCode`: Código del feature

Devuelve `int` o `null`

- `int`: Si se puede obtener el total
- `null`: Si el feature no está relacionado o no es del tipo `limit`

#### `getUsageOf(string $featureCode)`

Devuelve el uso de un feature relacionado a la suscripción

- `$featureCode`: Código del feature

Devuelve `int` o `null`

- `int`: Si se puede obtener el uso
- `null`: Si el feature no está relacionado o no es del tipo `limit`

#### `getRemainingOf(string $featureCode)`

Devuelve el uso restante de un feature relacionado a la suscripción

- `$featureCode`: Código del feature

Devuelve `int` o `null`

- `int`: Si se puede obtener el uso restante
- `null`: Si el feature no está relacionado o no es del tipo `limit`


## Relaciones

#### `period`

Devuelve el periodo relacionado

#### `subscriber`

Devuelve el modelo suscriptor asociado por la relación polimórfica

#### `plan_type`

Devuelve el tipo de plan asociado

## Scopes

#### `scopeByType($query, PlanType $planType)`

Filtra suscripciones por su tipo de plan

#### `scopeCanceled($query)`

Filtra suscripciones canceladas

#### `scopeFree($query)`

Filtra suscripciones gratuitas donde su campo `price` es `0`

#### `recurring($query)`

Filtra suscripciones recurrentes

# Eventos

Este paquete ofrece eventos que son lanzados en las diversas circunstancias más importantes o que nos permiten adaptar las suscripciones a la mayoria de casos posibles.

`Emeefe\Subscriptions\Events\FeatureLimitChangeOnPlan`
Se lanza cuando se actualiza el límite de un feature en un plan, tanto para la primera vez que se asigna límite como también cuando se actualiza.

- `$event->plan`: El plan al que se asigna el feature limit
- `$event->feature`: El feature al que se le asignará el límite
- `$event->limit`: El nuevo límite que se asigna

`Emeefe\Subscriptions\Events\PlanPeriodChange`
Se lanza cuando un periodo de plan es actualizado en alguno de los campos `price`, `currency`, `trial_days`, `period_unit`, `period_count`, `is_recurring`, `is_visible` o `tolerance_days`.

- `$event->oldPlanPeriod`: Antiguo periodo de plan
- `$event->newPlanPeriod`: Periodo de plan actualizado

`Emeefe\Subscriptions\Events\NewFeatureOnPlan`
Se lanza cuando un feature es asignado a un plan

- `$event->plan`: Plan al que se asignó el feature
- `$event->feature`: Feature asignado
- `$event->limit`: Límite definido en caso de ser feature del tipo `limit`, en otro caso es `null`

`Emeefe\Subscriptions\Events\NewSubscription`
Se lanza cuando un modelo se suscribe a un plan por medio de un periodo

- `$event->model`: El modelo que se suscribe
- `$event->subscription`: La suscripción creada

`Emeefe\Subscriptions\Events\RenewSubscription`
Se lanza cuando una suscripción es renovada/extendida

- `$event->model`: El modelo al que pertenece la suscripción
- `$event->subscription`: La suscripción que se renueva
- `$event->cycles`: Cantidad de ciclos a renovar

`Emeefe\Subscriptions\Events\CancelSubscription`
Se lanza cuando una subscripción es cancelada, usando el método `cancel`. Cuando es una cancelación por actualización de plan usando el método `updateSubscriptionTo` del trait `CanSubscribe` no se hace la llamada a este evento y se define su motivo de cancelación a `PlanSubscription::CANCEL_REASON_UPDATE_SUBSCRIPTION`

- `$event->subscription`: La suscripción cancelada
- `$event->reason`: El motivo de la cancelación proporcionado en `cancel($reason)`

`Emeefe\Subscriptions\Events\FeatureConsumed`
Se lanza cuando un feature de la suscripción es consumido

- `$event->subscription`: Suscripción de la cuál se consume
- `$event->model`: Modelo suscrito
- `$event->units`: Unidades consumidas

`Emeefe\Subscriptions\Events\FeatureUnconsumed`
Se lanza cuando un feature de la suscripción es "desconsumido"

- `$event->subscription`: Suscripción de la cuál se consume
- `$event->model`: Modelo suscrito
- `$event->units`: Unidades "desconsumidas"

`Emeefe\Subscriptions\Events\FeatureRemovedFromPlan`
Se lanza cuando un feature es eliminado del plan

- `$event->plan`: Plan del que se eliminó el feature
- `$event->feature`: Feature eliminado del plan

`Emeefe\Subscriptions\Events\UpdatedSubscription`
Se lanza cuando se actualiza suscripción usando el método `updateSubscriptionTo` del trait `CanSubscribe`

- `$event->model`: Modelo suscrito
- `$event->oldSubscription`: Suscripción anterior
- `$event->subscription`: Nueva suscripción