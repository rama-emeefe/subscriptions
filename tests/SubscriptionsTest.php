<?php

namespace Emeefe\Subscriptions\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use Subscriptions;
use Emeefe\Subscriptions\Models\PlanFeature;
use Emeefe\Subscriptions\Models\PlanType;
use Emeefe\Subscriptions\Models\Plan;
use Emeefe\Subscriptions\Exceptions\RepeatedCodeException;;


class SubscriptionsTest extends \Emeefe\Subscriptions\Tests\TestCase
{
    use RefreshDatabase;

    protected $planType;

    /**
     * Test plan features creation
     */
    public function test_create_plan_features(){
        $planFeatureLimit = $this->createPlanFeature('test_feature', 'limit');
        $this->assertSame($planFeatureLimit->type, 'limit');

        $planFeatureFeature = $this->createPlanFeature('test_feature', 'feature');
        $this->assertSame($planFeatureLimit->type, 'feature');
    }

    /**
     * Test plan feature metadata array casting
     */
    public function test_create_plan_feature_with_metadata(){
        $planFeature = $this->createPlanFeature('test_feature', 'limit', [
            'foo'=>'foo',
            'bar'=>'bar'
        ]);

        $this->assertArrayHasKey('foo', $planFeature->metadata);
        $this->assertArrayHasKey('bar', $planFeature->metadata);
    }

    /**
     * Test attach limit and unlimit features to plan
     * type, test the basic methods on plan type
     */
    public function test_attach_features_to_plan_type(){
        $limitFeature = $this->createPlanFeature('test_limit_feature', 'limit');
        $unlimitFeature = $this->createPlanFeature('test_unlimit_feature');

        $planType->attachFeature($limitFeature)
            ->attachFeature($unlimitFeature);

        $this->assertEquals($planType->features()->count(), 2);
        $this->assertTrue($planType->hasFeature('test_limit_feature'));
        $this->assertTrue($planType->hasFeature('test_unlimit_feature'));
        $this->assertFalse($planType->hasFeature('inexistent_feature'));

        $this->assertEquals($planType->getFeatureByCode('test_limit_feature')->id, $limitFeature->id);
        $this->assertNull($planType->getFeatureByCode('inexistent_feature'));
    }

    /**
     * Test plan creation
     */
    public function test_create_plan(){
        $planType = $this->createPlanType();
        $plan = $this->createPlan('test_plan', $planType);

        $this->assertNotNull($plan);
        $this->assertEquals($planType->id, $plan->type->id);
    }

    /**
     * Test plan creation and metadata array casting
     */
    public function test_create_plan_with_metadata(){
        $planType = $this->createPlanType();
        $plan = $this->createPlan('test_plan', $planType, false, [
            'foo'=>'foo',
            'bar'=>'bar'
        ]);

        $this->assertArrayHasKey('foo', $plan->metadata);
        $this->assertArrayHasKey('bar', $plan->metadata);
    }

    /**
     * Test the exception thrown when the plan code is 
     * repeated in the same type of plan
     */
    public function test_repeated_code_exception_in_same_type(){
        $planType = $this->createPlanType();

        $this->createPlan('test_code', $planType);
        $this->createPlan('test_code', $planType);

        $this->expectException(RepeatedCodeException::class);
    }

    /**
     * Test the creation of two plans of different types 
     * with the same code
     */
    public function test_repeated_code_in_different_type(){
        $firstPlanType = $this->createPlanType();
        $secondPlanType = $this->createPlanType();

        $firstPlan = $this->createPlan('test_code', $firstPlanType);
        $secondPlan = $this->createPlan('test_code', $secondPlanType);

        $this->assertSame($firstPlan->code, 'test_code');
        $this->assertSame($secondPlan->code, 'test_code');
    }

    /**
     * Test default plan of a type of plan
     */
    public function test_default_plan_on_type(){
        $planType = $this->createPlanType();
        $isDefault = true;

        $defaultPlan = $this->createPlan('test_code', $planType, $isDefault);

        $this->assertNotNull($planType->getDefaultPlan());
        $this->assertEquals($planType->getDefaultPlan()->id, $defaultPlan->id);
    }

    /**
     * Test inexistent default plan of a type of plan
     */
    public function test_inexistent_default_plan_on_type(){
        $planType = $this->createPlanType();
        $isDefault = false;

        $nonDefaultPlan = $this->createPlan('test_code', $planType, $isDefault);

        $this->assertNull($planType->getDefaultPlan());
    }

    /**
     * Test replacement of default plan
     */
    public function test_replacement_of_default_plan(){
        $planType = $this->createPlanType();
        $isDefault = true;

        $firstDefaultPlan = $this->createPlan('first_plan', $planType, $isDefault);
        $this->assertEquals($planType->getDefaultPlan()->id, $firstDefaultPlan->id);

        $secondDefaultPlan = $this->createPlan('second_plan', $planType, $isDefault);
        $this->assertEquals($planType->getDefaultPlan()->id, $secondDefaultPlan->id);

        $this->assertFalse($firstDefaultPlan->is_default);
    }

    /**
     * Test the visibility of plans and scopes to obtain them
     */
    public function test_plans_visibility(){
        $planType = $this->createPlanType();

        $isVisible = true;
        $visiblePlan = $this->createPlan('visible_plan', $planType, false, [], $isVisible);

        $isVisible = true;
        $visiblePlan = $this->createPlan('hidden_plan', $planType, false, [], $isVisible);

        $this->assertEquals($planType->plans()->visible()->count(), 1);
        $this->assertEquals($planType->plans()->hidden()->count(), 1);
        $this->assertEquals($planType->plans()->count(), 2);
    }

    public function test_attach_features_to_plan(){
        $planType = $this->createPlanType();
        
        $imagesFeature = $this->createPlanFeature('images_feature', 'limit');
        $premiumFeature = $this->createPlanFeature('premium_feature');

        $planType->attachFeature($imagesFeature)
            ->attachFeature($premiumFeature);

        $plan = $this->createPlan('test_plan', $planType);

        $this->assertTrue($plan->assignFeatureLimitByCode('images_feature', 10));
        $this->assertFalse($plan->assignFeatureLimitByCode('premium_feature', 5));
        $this->assertFalse($plan->assignFeatureLimitByCode('inexistent_feature', 50));
        
        $this->assertEquals($plan->getFeatureLimitByCode('images_feature'), 10);
        $this->assertEquals($plan->getFeatureLimitByCode('premium_feature'), -1);
        $this->assertEquals($plan->getFeatureLimitByCode('inexistent_feature'), -1);

        $this->assertTrue($plan->hasFeature('images_feature'));
        $this->assertTrue($plan->hasFeature('premium_feature'));
        $this->assertFalse($plan->hasFeature('inexistent_feature'));
    }

    public function test_create_plan_periods_with_default_data(){
        $planType = $this->createPlanType();
        $plan = $this->createPlan('test_plan_for_periods', $planType);

        $planPeriod = Subscriptions::period($this->faker->sentence(3), 'test_plan_for_periods_period', $plan)
            ->create();

        $this->assertEquals($planPeriod->price, 0);
        $this->assertSame($planPeriod->currency, 'MXN');
        $this->assertEquals($planPeriod->trial_days, 0);
        $this->assertNull($planPeriod->period_unit);
        $this->assertNull($planPeriod->period_count);
        $this->assertFalse($planPeriod->is_recurring);
        $this->assertTrue($planPeriod->isInfinite());
        $this->assertFalse($planPeriod->isFinite());
        $this->assertFalse($planPeriod->is_hidden);
        $this->assertEquals($planPeriod->tolerance_days, 0);
    }

    public function test_create_plan_periods_in_recurrent_plan(){
        $planType = $this->createPlanType();
        $plan = $this->createPlan('recurrent_plan', $planType);

        $planPeriod = Subscriptions::period($this->faker->sentence(3), 'monthly_period', $plan)
            ->setPrice(100)
            ->setTrialDays(10)
            ->setRecurringPeriod(1, 'month')
            ->create();

        $this->assertEquals($planPeriod->price, 0);
        $this->assertSame($planPeriod->currency, 'MXN');
        $this->assertEquals($planPeriod->trial_days, 0);
        $this->assertNull($planPeriod->period_unit);
        $this->assertNull($planPeriod->period_count);
        $this->assertFalse($planPeriod->is_recurring);
        $this->assertFalse($planPeriod->is_hidden);
        $this->assertEquals($planPeriod->tolerance_days, 0);
    }

    /**
     * Create a PlanType instance
     * 
     * @return Emeefe\Subscriptions\PlanType
     */
    public function createPlanType(){
        $planType = new PlanType();
        $planType->type = $this->faker->word;
        $planType->description = $this->faker->text();
        $planType->save();

        return $planType;
    }

    /**
     * Create a PlanFeature instance
     * 
     * @param string $code
     * @param string $type
     * @param array  $metadata
     * @return Emeefe\Subscriptions\PlanFeature
     */
    public function createPlanFeature(string $code, string $type = 'feature', $metadata = null){
        $planFeature = new PlanFeature();
        $planFeature->display_name = $this->faker->sentence(3);
        $planFeature->code = $code;
        $planFeature->description = $this->faker->text();
        $planFeature->type = $type;
        $planFeature->metadata = $metadata;
        $planFeature->save();

        return $planFeature;
    }

    /**
     * Create a Plan instance
     * 
     * @param string   $code
     * @param PlanType $type
     * @param bool     $isDefault
     * @param array    $metadata
     * @return Emeefe\Subscriptions\Plan
     */
    public function createPlan(string $code, PlanType $type, bool $isDefault = false, $metadata = null, bool $isVisible = false){
        $plan = new Plan();
        $plan->display_name = $this->faker->sentence(3);
        $plan->code = $code;
        $plan->description = $this->faker->text();
        $plan->type_id = $type->id;
        $plan->is_default = $isDefault;
        $plan->metadata = $metadata;
        $plan->is_visible = $isVisible;
        $plan->save();

        return $plan;
    }
}
