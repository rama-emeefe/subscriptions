# Subscriptions

## Tipos de plan (PlanType)
Un tipo de plan engloba un conjunto de caracteristicas permitidas, puede usarse para separar multiples tipos de planes como planes para Empresa, para Usuarios, de almacenamiento, etc.

- Un tipo de plan puede estar ligado a una o más características.

## Características de plan (PlanFeature)
Las caracteristicas de plan, como su nombre lo dice, permiten definir características, permisos, etc que puede tener un tipo de plan.

- Una característica puede ser contable(`limit`) o no contable(`feature`).
- Una característica no almacena los límites que tiene sino solo su información básica.
- Una característica puede estar ligada a uno o varios tipos de plan permitiendo casos en los que distintos tipos de plan comparten una misma caracteristica.

## Planes (Plan)
Un plan pertenece a un tipo de plan y este puede tener asociados los límites de las características del tipo de plan, siempre y cuando sean carcateristicas contables(`limit`).

- Plan default: Un plan default es el plan que se obtiene por defecto al suscribir a un usuario u otra instancia, solo puede existir un plan default.
- Un plan pertenece solo a un tipo de plan
- Un plan puede tener visibilidad pública o privada

## Periodos de plan (PlanPeriod)
Un periodo de plan indica el tiempo que dura un ciclo, los usuarios o instancias se suscriben directamente al periodo y no al plan dado que os periodos pueden variar en tiempo.

- Un periodo puede tener un costo o ser gratuito
- Un periodo pertenece a un solo plan
- Puede tener dias de prueba
- Puede variar en duración
    - Puede ser recurrente: Cada cierto tiempo debe renovarse(`recurring`)
    - Puede ser no recurrente: No puede renovarse
        - Puede ser finito: Tiene definido una unidad de tiempo como Día, Mes y Año además de la cantidad de unidades de tiempo, por ejemplo **5 días**, **6 meses**, **1 año**, pasado este periodo no se vuelve a repetir, termina la suscripción. (`finite`)
        - Puede ser infinito: Puede no tener definido una unidad de tiempo ni cantidad de unidades, en otras palabras, **nunca caduca**. (`infinite`)
- Un plan puede tener visibilidad pública o privada
- Puede tener días de tolerancia para renovación.

## Subscripción
Una subscripción es una relación creada entre un usuario u otra instancia con un plan a través de un periodo, la suscripción obtiene informacion actual del plan y del periodo y la mantiene en la suscripción, parecido a crear una copia, para evitar efectos colaterales cuando cambian datos del plan, precios, etc. pudiendo definir el comportamiento cuando ocurren estas situaciones a través de la escucha de eventos.

Cuando se crea una suscripción, esta se vuelve independiente del plan y del periodo aunque es posible obtener las relaciones a dichos modelos para obtener información actualizada u otras acciones necesarias.

- La suscripción trabaja con relaciones polimórficas pudiendo tener relaciones con cualquier modelo y no solo con el de usuario.
- 