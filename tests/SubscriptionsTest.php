<?php

namespace Emeefe\Subscriptions\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Subscriptions;
use Emeefe\Subscriptions\Models\PlanFeature;
use Emeefe\Subscriptions\Models\PlanType;
use Emeefe\Subscriptions\Models\Plan;
use Emeefe\Subscriptions\Models\PlanPeriod;
use Emeefe\Subscriptions\Exceptions\RepeatedCodeException;
use Carbon\Carbon;

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
        $this->assertSame($planFeatureFeature->type, 'feature');
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

        $planType = $this->createPlanType('user_plan');
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
     * Test ignorance of assignment of existing features
     */
    public function test_attach_existent_features_to_plan_type(){
        $limitFeature = $this->createPlanFeature('test_limit_feature', 'limit');

        $planType = $this->createPlanType('user_plan');
        $planType->attachFeature($limitFeature)
            ->attachFeature($limitFeature)
            ->attachFeature($limitFeature);

        $this->assertEquals($planType->features()->count(), 1);
        $this->assertTrue($planType->hasFeature('test_limit_feature'));
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
        $this->expectException(RepeatedCodeException::class);

        $planType = $this->createPlanType();
        
        $this->createPlan('test_code', $planType);
        $this->createPlan('test_code', $planType);
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

        $firstDefaultPlan = $this->createPlan('first1_plan', $planType, $isDefault);
        $this->assertEquals($planType->getDefaultPlan()->id, $firstDefaultPlan->id);

        $secondDefaultPlan = $this->createPlan('second_plan', $planType, $isDefault);
        $this->assertEquals($planType->getDefaultPlan()->id, $secondDefaultPlan->id);

        $firstDefaultPlan->refresh();
        $this->assertFalse($firstDefaultPlan->is_default);
    }

    /**
     * Test the visibility of plans and scopes to obtain them
     */
    public function test_plans_visibility(){
        $planType = $this->createPlanType();

        $isVisible = true;
        $visiblePlan = $this->createPlan('visible_plan', $planType, false, [], $isVisible);

        $isVisible = false;
        $visiblePlan = $this->createPlan('hidden_plan', $planType, false, [], $isVisible);

        $this->assertEquals($planType->plans()->visible()->count(), 1);
        $this->assertEquals($planType->plans()->hidden()->count(), 1);
        $this->assertEquals($planType->plans()->count(), 2);
    }

    /**
     * Test attach features to the plan with limits and 
     * without limits
     */
    public function test_attach_features_to_plan(){
        $planType = $this->createPlanType();
        
        $imagesFeature = $this->createPlanFeature('images_feature', 'limit');
        $mbStorageFeature = $this->createPlanFeature('mb_storage', 'limit');
        $premiumFeature = $this->createPlanFeature('premium_feature');

        $planType->attachFeature($imagesFeature)
            ->attachFeature($premiumFeature);

        $plan = $this->createPlan('test_plan', $planType);
        $plan->refresh();
        $this->assertFalse($plan->assignFeatureLimitByCode('images_feature', -2));
        $this->assertFalse($plan->assignFeatureLimitByCode('images_feature', 0));
        $this->assertTrue($plan->assignFeatureLimitByCode('images_feature', 10));
        $this->assertFalse($plan->assignFeatureLimitByCode('premium_feature', 5));
        $this->assertFalse($plan->assignFeatureLimitByCode('inexistent_feature', 50));

        $this->assertTrue($plan->assignUnlimitFeatureByCode('premium_feature'));
        $this->assertFalse($plan->assignUnlimitFeatureByCode('images_feature'));
        
        $this->assertEquals($plan->getFeatureLimitByCode('images_feature'), 10);
        $this->assertTrue($plan->assignFeatureLimitByCode('images_feature', 15));
        $plan->refresh();
        
        $this->assertEquals($plan->getFeatureLimitByCode('images_feature'), 15);
        $this->assertEquals($plan->getFeatureLimitByCode('premium_feature'), -1);
        $this->assertEquals($plan->getFeatureLimitByCode('inexistent_feature'), -1);
        $this->assertEquals($plan->getFeatureLimitByCode('mb_storage'), -1);

        $this->assertTrue($plan->hasFeature('images_feature'));
        $this->assertTrue($plan->hasFeature('premium_feature'));
        $this->assertFalse($plan->hasFeature('inexistent_feature'));
    }

    /**
     * Test values assigned by default to a period when using PeriodBuilder
     */
    public function test_plan_period_builder_default_values(){
        $planType = $this->createPlanType();
        $plan = $this->createPlan('test_plan', $planType);

        $planPeriod = Subscriptions::period($this->faker->sentence(3), 'test_period', $plan)
            ->create();

        $this->assertEquals($planPeriod->price, 0);
        $this->assertSame($planPeriod->currency, 'MXN');
        $this->assertEquals($planPeriod->trial_days, 0);
        $this->assertNull($planPeriod->period_unit);
        $this->assertNull($planPeriod->period_count);
        $this->assertFalse($planPeriod->is_recurring);
        $this->assertTrue($planPeriod->is_visible);
        $this->assertEquals($planPeriod->tolerance_days, 0);
        $this->assertFalse($planPeriod->is_default);
    }

    /**
     * Test the price allocation for a period
     */
    public function test_plan_period_builder_price(){
        $planType = $this->createPlanType();
        $plan = $this->createPlan('recurrent_plan', $planType);

        //Negative price
        $planNegativePricePeriod = Subscriptions::period($this->faker->sentence(3), 'monthly_period', $plan)
            ->setPrice(-100)
            ->create();

        $this->assertEquals($planNegativePricePeriod->price, 0);
        $this->assertTrue($planNegativePricePeriod->isFree());

        //Correct price
        $planCorrectPricePeriod = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->setPrice(100)
            ->create();

        $this->assertEquals($planCorrectPricePeriod->price, 100);
    }

    /**
     * Test the trial days allocation for a period
     */
    public function test_plan_period_builder_trial_days(){
        $planType = $this->createPlanType();
        $plan = $this->createPlan('recurrent_plan', $planType);

        //Negative trial days
        $planNegativeTrialPeriod = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->setTrialDays(-5)
            ->create();

        $this->assertEquals($planNegativeTrialPeriod->trial_days, 0);
        $this->assertFalse($planNegativeTrialPeriod->hasTrial());

        //Correct trial days
        $planCorrectTrailPeriod = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->setLimitedNonRecurringPeriod(12, PlanPeriod::UNIT_MONTH)
            ->setTrialDays(5)
            ->create();

        $this->assertEquals($planCorrectTrailPeriod->trial_days, 5);
        $this->assertTrue($planCorrectTrailPeriod->hasTrial());
    }

    /**
     * Test recurring period
     */
    public function test_plan_period_builder_recurring_period(){
        $planType = $this->createPlanType();
        $plan = $this->createPlan('recurrent_plan', $planType);

        $recurrentPeriod = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->setRecurringPeriod(6, PlanPeriod::UNIT_MONTH)
            ->create();

        $this->assertTrue($recurrentPeriod->isRecurring());
        $this->assertFalse($recurrentPeriod->isLimitedNonRecurring());
        $this->assertFalse($recurrentPeriod->isUnlimitedNonRecurring());
        $this->assertEquals($recurrentPeriod->period_count, 6);
        $this->assertSame($recurrentPeriod->period_unit, PlanPeriod::UNIT_MONTH);
    }

    /**
     * Test limited non recurring period
     */
    public function test_plan_period_builder_limited_non_recurring_period(){
        $planType = $this->createPlanType();
        $plan = $this->createPlan('non_recurrent_plan', $planType);

        $nonRecurrentPeriod = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->setLimitedNonRecurringPeriod(1, PlanPeriod::UNIT_YEAR)
            ->create();

        $this->assertFalse($nonRecurrentPeriod->isRecurring());
        $this->assertTrue($nonRecurrentPeriod->isLimitedNonRecurring());
        $this->assertFalse($nonRecurrentPeriod->isUnlimitedNonRecurring());
        $this->assertEquals($nonRecurrentPeriod->period_count, 1);
        $this->assertSame($nonRecurrentPeriod->period_unit, PlanPeriod::UNIT_YEAR);
    }

    /**
     * Test unlimited non recurring period
     */
    public function test_plan_period_builder_unlimited_non_recurring_period(){
        $planType = $this->createPlanType();
        $plan = $this->createPlan('non_recurrent_plan', $planType);

        $nonRecurrentPeriod = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->create();

        $this->assertFalse($nonRecurrentPeriod->isRecurring());
        $this->assertFalse($nonRecurrentPeriod->isLimitedNonRecurring());
        $this->assertTrue($nonRecurrentPeriod->isUnlimitedNonRecurring());
        $this->assertNull($nonRecurrentPeriod->period_count);
        $this->assertNull($nonRecurrentPeriod->period_unit);
    }

    /**
     * Test visibility period
     */
    public function test_plan_period_visibility(){
        $planType = $this->createPlanType();
        $plan = $this->createPlan('test_plan', $planType);

        $period = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->setHidden()
            ->create();

        $this->assertTrue($period->isHidden());
        $this->assertFalse($period->isVisible());

        $period->setAsVisible();

        $this->assertFalse($period->isHidden());
        $this->assertTrue($period->isVisible());

        $period->setAsHidden();

        $this->assertTrue($period->isHidden());
        $this->assertFalse($period->isVisible());
    }

    /**
     * Test tolerance days allocation for a period
     */
    public function test_plan_period_builder_tolerance_days(){
        $planType = $this->createPlanType();
        $plan = $this->createPlan('plan', $planType);

        //Negative tolerance days
        $planNegativeTolerancePeriod = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->setToleranceDays(-5)
            ->create();

        $this->assertEquals($planNegativeTolerancePeriod->tolerance_days, 0);

        //Correct tolerance days
        $planCorrectTolerancePeriod = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->setLimitedNonRecurringPeriod(12, PlanPeriod::UNIT_MONTH)
            ->setToleranceDays(5)
            ->create();

        $this->assertEquals($planCorrectTolerancePeriod->tolerance_days, 5);
    }

    /**
     * Test default period, the saving event is tested
     */
    public function test_plan_period_default(){
        $planType = $this->createPlanType();
        $plan = $this->createPlan('plan', $planType);

        $nonDefaultPeriod = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->create();
        $defaultPeriod = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->setDefault()
            ->create();

        $this->assertFalse($nonDefaultPeriod->isDefault());
        $this->assertTrue($defaultPeriod->isDefault());

        $nonDefaultPeriod->setAsDefault();
        $nonDefaultPeriod->refresh();
        $defaultPeriod->refresh();

        $this->assertTrue($nonDefaultPeriod->isDefault());
        $this->assertFalse($defaultPeriod->isDefault());
    }

    /**
     * Test subscription to one type of plan over only one of its periods,
     * test existence
     */
    public function test_unique_not_cancelled_subscription_in_plan_type(){
        $planType = $this->createPlanType('user_membership');
        $plan = $this->createPlan('plan', $planType);
        $periodOne = Subscriptions::period($this->faker->sentence(3), 'period_one', $plan)
            ->create();
        $periodTwo = Subscriptions::period($this->faker->sentence(3), 'period_two', $plan)
            ->create();

        $user = $this->createUser();

        $this->assertTrue($user->subscribeTo($periodOne));
        $this->assertFalse($user->subscribeTo($periodTwo));
        $this->assertTrue($user->hasSubscription($planType));
        $this->assertTrue($user->hasSubscription('user_membership'));
    }

    /**
     * Test subscription with trial days
     */
    public function test_subscription_trial(){
        Carbon::setTestNow(Carbon::create(2020, 1, 1, 12, 0, 0));

        $planType = $this->createPlanType('user_membership');
        $plan = $this->createPlan('plan', $planType);
        $period = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->setLimitedNonRecurringPeriod(12, PlanPeriod::UNIT_MONTH)
            ->setTrialDays(10)
            ->create();

        $user = $this->createUser();
        $user->subscribeTo($period);
        $currentSubscription = $user->currentSubscription($planType);

        $this->assertEquals($user->subscriptions()->byType($planType)->count(), 1);
        $this->assertNotNull($currentSubscription);
        $this->assertTrue($currentSubscription->isOnTrial());
        $this->assertEquals($currentSubscription->remainingTrialDays(), 9);

        Carbon::setTestNow(Carbon::create(2020, 1, 11, 12, 0, 1));

        $this->assertFalse($currentSubscription->isOnTrial());
        $this->assertEquals($currentSubscription->remainingTrialDays(), 0);

        Carbon::setTestNow();   
    }

    /**
     * Test subscription without trial days
     */
    public function test_subscription_without_trial(){
        Carbon::setTestNow(Carbon::create(2020, 1, 1, 12, 0, 0));

        $planType = $this->createPlanType('user_membership');
        $plan = $this->createPlan('plan', $planType);
        $period = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->create();

        $user = $this->createUser();
        $user->subscribeTo($period);
        $currentSubscription = $user->currentSubscription($planType);

        $this->assertFalse($currentSubscription->isOnTrial());
        $this->assertEquals($currentSubscription->remainingTrialDays(), 0);

        Carbon::setTestNow();   
    }

    /**
     * Test recurring subscription with trial days and tolerance days
     * Trial start          -> 2020, 1, 1, 12, 0    3 days
     * Start                -> 2020, 1, 4, 12, 0    1 Month
     * Expiration tolerance -> 2020, 2, 4, 12, 0    5 days
     * End tolerance days   -> 2020, 2, 9, 12, 0    2 Months
     * Renew                -> 2020, 4, 9, 12, 0
     */
    public function test_recurring_subscription_status_trial_and_tolerance(){
        Carbon::setTestNow(Carbon::create(2020, 1, 1, 12, 0, 0));

        $planType = $this->createPlanType('user_membership');
        $plan = $this->createPlan('plan', $planType);
        $period = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->setRecurringPeriod(1, PlanPeriod::UNIT_MONTH)
            ->setTrialDays(3)
            ->setToleranceDays(5)
            ->create();

        $user = $this->createUser();
        $user->subscribeTo($period);
        $currentSubscription = $user->currentSubscription($planType);

        $this->assertTrue($currentSubscription->isOnTrial());
        $this->assertEquals($currentSubscription->remainingTrialDays(), 2);
        $this->assertTrue($period->isRecurring());
        $this->assertFalse($currentSubscription->isActive());
        $this->assertTrue($currentSubscription->isValid());

        Carbon::setTestNow(Carbon::create(2020, 1, 4, 12, 0, 1));

        $this->assertFalse($currentSubscription->isOnTrial());
        $this->assertTrue($currentSubscription->isActive());
        $this->assertTrue($currentSubscription->isValid());
        $this->assertFalse($currentSubscription->isExpiredWithTolerance());
        $this->assertFalse($currentSubscription->isFullExpired());

        Carbon::setTestNow(Carbon::create(2020, 2, 4, 12, 0, 1));

        $this->assertFalse($currentSubscription->isActive());
        $this->assertTrue($currentSubscription->isValid());
        $this->assertTrue($currentSubscription->isExpiredWithTolerance());
        $this->assertFalse($currentSubscription->isFullExpired());

        Carbon::setTestNow(Carbon::create(2020, 2, 9, 12, 0, 1));

        $this->assertFalse($currentSubscription->isActive());
        $this->assertFalse($currentSubscription->isValid());
        $this->assertFalse($currentSubscription->isExpiredWithTolerance());
        $this->assertTrue($currentSubscription->isFullExpired());

        $this->assertTrue($currentSubscription->renew(2));

        $this->assertTrue($currentSubscription->isActive());
        $this->assertTrue($currentSubscription->isValid());
        $this->assertFalse($currentSubscription->isExpiredWithTolerance());
        $this->assertFalse($currentSubscription->isFullExpired());

        Carbon::setTestNow(Carbon::create(2020, 4, 9, 12, 0, 2));

        $this->assertTrue($currentSubscription->cancel('non-payment'));
        $this->assertTrue($currentSubscription->isCanceled());
        $this->assertTrue($currentSubscription->isFullExpired());

        $this->assertFalse($currentSubscription->renew(2));

        Carbon::setTestNow();
    }

    /**
     * Test non recurring limited subscription 
     * 
     * Trial start          -> 2020, 1, 1, 12, 0    0 days
     * Start                -> 2020, 1, 1, 12, 0    12 Months
     * Expiration tolerance -> 2021, 1, 1, 12, 0    0 days
     * End tolerance days   -> 2021, 1, 1, 12, 0   
     */
    public function test_non_recurring_limited_subscription_status(){
        Carbon::setTestNow(Carbon::create(2020, 1, 1, 12, 0, 0));

        $planType = $this->createPlanType('user_membership');
        $plan = $this->createPlan('plan', $planType);
        $period = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->setLimitedNonRecurringPeriod(12, PlanPeriod::UNIT_MONTH)
            ->create();

        $user = $this->createUser();
        $user->subscribeTo($period);
        $currentSubscription = $user->currentSubscription($planType);

        $this->assertTrue($currentSubscription->isActive());
        $this->assertTrue($currentSubscription->isValid());

        Carbon::setTestNow(Carbon::create(2020, 12, 1, 12, 0, 0));

        $this->assertTrue($currentSubscription->isActive());
        $this->assertTrue($currentSubscription->isValid());

        Carbon::setTestNow(Carbon::create(2021, 1, 1, 12, 0, 0));

        $this->assertFalse($currentSubscription->isActive());
        $this->assertFalse($currentSubscription->isValid());
        $this->assertFalse($currentSubscription->isExpiredWithTolerance());
        $this->assertTrue($currentSubscription->isFullExpired());

        $this->assertFalse($currentSubscription->renew(1));

        Carbon::setTestNow();
    }

    /**
     * Test non recurring ilimited subscription 
     */
    public function test_non_recurring_ilimited_subscription_status(){
        Carbon::setTestNow(Carbon::create(2020, 1, 1, 12, 0, 0));

        $planType = $this->createPlanType('user_membership');
        $plan = $this->createPlan('plan', $planType);
        $period = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->setTrialDays(100) //will be ignored *
            ->setToleranceDays(20) //will be ignored
            ->create();

        $user = $this->createUser();
        $user->subscribeTo($period);
        $currentSubscription = $user->currentSubscription($planType);

        $this->assertTrue($currentSubscription->isActive());
        $this->assertTrue($currentSubscription->isValid());
        $this->assertFalse($currentSubscription->isExpiredWithTolerance());
        $this->assertFalse($currentSubscription->isFullExpired());

        $this->assertFalse($currentSubscription->renew());

        // 10 Years later
        Carbon::setTestNow(Carbon::create(2030, 1, 1, 12, 0, 0));

        $this->assertTrue($currentSubscription->isActive());
        $this->assertTrue($currentSubscription->isValid());

        $this->assertTrue($currentSubscription->cancel());

        $this->assertFalse($currentSubscription->isActive());
        $this->assertFalse($currentSubscription->isValid());
        $this->assertFalse($currentSubscription->isExpiredWithTolerance());
        $this->assertTrue($currentSubscription->isFullExpired());

        Carbon::setTestNow();
    }

    /**
     * Test non recurring ilimited subscription 
     */
    public function test_subscription_features(){
        $imagesFeature = $this->createPlanFeature('images_feature', 'limit');
        $premiumFeature = $this->createPlanFeature('premium_feature');
        $nonLimitAssignedFeature = $this->createPlanFeature('non_limit_assigned');

        $planType = $this->createPlanType('user_membership')
            ->attachFeature($imagesFeature)
            ->attachFeature($premiumFeature)
            ->attachFeature($nonLimitAssignedFeature);

        $plan = $this->createPlan('plan', $planType);
        $plan->assignFeatureLimitByCode('images_feature', 10);
        $plan->assignUnlimitFeatureByCode('premium_feature');
        $plan->assignUnlimitFeatureByCode('non_limit_assigned');

        $period = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->create();

        $user = $this->createUser();
        $user->subscribeTo($period);
        $currentSubscription = $user->currentSubscription($planType);

        $this->assertTrue($currentSubscription->hasFeature('images_feature'));
        $this->assertTrue($currentSubscription->hasFeature('premium_feature'));
        $this->assertTrue($currentSubscription->hasFeature('non_limit_assigned'));
        $this->assertFalse($currentSubscription->hasFeature('inexistent_feature'));

        $this->assertFalse($currentSubscription->unconsumeFeature('images_feature'));
        $this->assertTrue($currentSubscription->consumeFeature('images_feature', 3));
        $this->assertEquals($currentSubscription->getUsageOf('images_feature'), 3);
        $this->assertEquals($currentSubscription->getRemainingOf('images_feature'), 7);
        $this->assertFalse($currentSubscription->consumeFeature('images_feature', 8));
        $this->assertEquals($currentSubscription->getRemainingOf('images_feature'), 7);
        $this->assertTrue($currentSubscription->unconsumeFeature('images_feature'));
        $this->assertTrue($currentSubscription->consumeFeature('images_feature', 8));
        $this->assertEquals($currentSubscription->getUsageOf('images_feature'), 10);

        $this->assertFalse($currentSubscription->unconsumeFeature('premium_feature'));
        $this->assertFalse($currentSubscription->consumeFeature('premium_feature', 3));
        $this->assertNull($currentSubscription->getRemainingOf('premium_feature'));
        $this->assertNull($currentSubscription->getUsageOf('premium_feature'));   

        $this->assertFalse($currentSubscription->unconsumeFeature('premium_feature'));
        $this->assertFalse($currentSubscription->consumeFeature('premium_feature'));
        $this->assertEquals($currentSubscription->getRemainingOf('premium_feature'), 0);
    }

    /**
     * Test renew subscription
     */
    public function test_dates_subscription_and_renew(){
        Carbon::setTestNow(Carbon::create(2020, 1, 28, 12, 0, 0)); //28 Jan 2020 12:00:00

        $planType = $this->createPlanType('user_membership');
        $plan = $this->createPlan('plan', $planType);
        $period = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->setRecurringPeriod(1, PlanPeriod::UNIT_MONTH)
            ->setTrialDays(3)
            ->setToleranceDays(5)
            ->create();

        $user = $this->createUser();
        $user->subscribeTo($period);
        $currentSubscription = $user->currentSubscription($planType);

        $this->assertSame($currentSubscription->trial_starts_at->format('Y-m-d H:i'), '2020-01-28 12:00');
        $this->assertSame($currentSubscription->starts_at->format('Y-m-d H:i'), '2020-01-31 12:00');
        $this->assertSame($currentSubscription->expires_at->format('Y-m-d H:i'), '2020-02-29 12:00');

        $currentSubscription->renew(2);

        $this->assertSame($currentSubscription->trial_starts_at->format('Y-m-d H:i'), '2020-01-28 12:00');
        $this->assertSame($currentSubscription->starts_at->format('Y-m-d H:i'), '2020-01-31 12:00');
        $this->assertSame($currentSubscription->expires_at->format('Y-m-d H:i'), '2020-04-30 12:00');

        $currentSubscription->renew(1);

        $this->assertSame($currentSubscription->expires_at->format('Y-m-d H:i'), '2020-05-31 12:00');
    }

    /**
     * Test renew subscription
     */
    public function test_dates_not_end_month_subscription_and_renew(){
        Carbon::setTestNow(Carbon::create(2020, 1, 29, 12, 0, 0)); //29 Jan 2020 12:00:00

        $planType = $this->createPlanType('user_membership');
        $plan = $this->createPlan('plan', $planType);
        $period = Subscriptions::period($this->faker->sentence(3), 'period', $plan)
            ->setRecurringPeriod(1, PlanPeriod::UNIT_MONTH)
            ->setTrialDays(0)
            ->setToleranceDays(0)
            ->create();

        $user = $this->createUser();
        $user->subscribeTo($period);
        $currentSubscription = $user->currentSubscription($planType);

        $this->assertSame($currentSubscription->starts_at->format('Y-m-d H:i'), '2020-01-29 12:00');
        $this->assertSame($currentSubscription->expires_at->format('Y-m-d H:i'), '2020-02-29 12:00');

        $currentSubscription->renew(2);

        $this->assertSame($currentSubscription->expires_at->format('Y-m-d H:i'), '2020-04-29 12:00');

        $currentSubscription->renew(1);

        $this->assertSame($currentSubscription->expires_at->format('Y-m-d H:i'), '2020-05-29 12:00');
    }


    /**
     * Create a PlanType instance
     * 
     * @return Emeefe\Subscriptions\PlanType
     */
    public function createPlanType($type = null){
        $planType = new PlanType();
        $planType->type = $type ? :$this->faker->word;
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
        $plan->plan_type_id = $type->id;
        $plan->is_default = $isDefault;
        $plan->metadata = $metadata;
        $plan->is_visible = $isVisible;
        $plan->save();
        return $plan;       
    }
}
