<?php
header("content-type: application/json");

require("impactEstimator.php");

function covid19ImpactEstimator($data)
{
    $estimator = new Estimate();
    $severeEstimator = new Estimate();
    $result = array();
    $result['data'] = $data;
    $result['impact'] = $estimator->process($data);
    $result['severeImpact'] = $severeEstimator->process($data, true);

    return $result;
}
