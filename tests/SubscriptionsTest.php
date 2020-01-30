<?php

namespace Emeefe\Subscriptions\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use Subscriptions;
use Emeefe\Subscriptions\Models\PlanFeature;
use Emeefe\Subscriptions\Models\PlanType;
use Emeefe\Subscriptions\Models\Plan;


class SubscriptionsTest extends \Emeefe\Subscriptions\Tests\TestCase
{
    public function test_create_plan_type(){
        $planType = $this->createPlanType();
        $this->assertNotNull($planType);
    }

    public function test_create_plan_features(){
        $planFeatureLimit = $this->createPlanFeature('test_feature', 'limit');
        $this->assertSame($planFeatureLimit->type, 'limit');

        $planFeatureFeature = $this->createPlanFeature('test_feature', 'feature');
        $this->assertSame($planFeatureLimit->type, 'feature');
    }

    public function test_create_plan_feature_with_metadata(){
        $planFeature = $this->createPlanFeature('test_feature', 'limit', [
            'foo'=>'foo',
            'bar'=>'bar'
        ]);

        $this->assertArrayHasKey('foo', $planFeature->metadata);
        $this->assertArrayHasKey('bar', $planFeature->metadata);
    }

    public function test_attach_features_to_plan_type(){
        $planType = $this->createPlanType();

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

    public function test_create_plan(){
        $planType = $this->createPlanType();
        $plan = $this->createPlan('free_plan', $planType);

        $this->assertNotNull($plan);
        $this->assertEquals($planType->id, $plan->type->id);
    }

    public function test_create_plan_with_metadata(){
        $planType = $this->createPlanType();
        $plan = $this->createPlan('free_plan', $planType, false, [
            'foo'=>'foo',
            'bar'=>'bar'
        ]);

        $this->assertArrayHasKey('foo', $plan->metadata);
        $this->assertArrayHasKey('bar', $plan->metadata);
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
    public function createPlan(string $code, PlanType $type, bool $isDefault = false, $metadata = null){
        $plan = new Plan();
        $plan->display_name = $this->faker->sentence(3);
        $plan->code = $code;
        $plan->description = $this->faker->text();
        $plan->type_id = $type->id;
        $plan->is_default = $isDefault;
        $plan->metadata = $metadata;
        $plan->is_hidden = false;
        $plan->save();

        return $plan;
    }
}
