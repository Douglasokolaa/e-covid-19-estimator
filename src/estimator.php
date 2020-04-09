<?php
header("content-type: application/json");

require("impactEstimator.php");

function covid19ImpactEstimator($data)
{
    $data = (object) $data;
    $estimator = new Estimate();
    $severeEstimator = new Estimate();

    $result = new stdClass;
    $result->data = $data;
    $result->impact = $estimator->process($data);
    $result->severeImpact = $severeEstimator->process($data, true);

    return json_encode($result);
}
