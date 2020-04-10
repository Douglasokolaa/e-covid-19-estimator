<?php

class Estimate
{
    public $avgDailyIncomeInUSD;
    public $avgDailyIncomePopulation;
    public $reportedCases;
    public $totalHospitalBeds;
    public $result;
    public $days;
    public $currentlyInfected;

    public function __construct()
    {
        $this->result = array();
    }

    public function toDays($period, $periodType)
    {
        $days = $period;

        if ($periodType === "weeks") {
            $days = 7 * $period;
        } elseif ($periodType === "months") {
            $days = 30 * $period;
        }
        return $days;
    }

    public function infectionsByRequestedTime($reportedCases, $severe = false)
    {
        $impact = (!$severe) ? 10 : 50;

        $factor = (floor($this->days / 3)) ? floor($this->days / 3) : $this->days;

        $currentlyInfected = $reportedCases * $impact;

        $this->currentlyInfected = $currentlyInfected;

        $result = $currentlyInfected * pow(2, $factor);

        return floor($result);
    }

    public function severeCasesByRequestedTime($infectionsByRequestedTime)
    {
        return floor(0.15 * $infectionsByRequestedTime);
    }

    public function hospitalBedsByRequestedTime( $severeCases, $totalHospitalBeds)
    {
        $beds4Covid19 = (35/100) * $totalHospitalBeds;

        $available = $beds4Covid19 - $severeCases;

        if ($available < 0) return ceil($available);

        return floor($available);
    }

    public function casesForICUByRequestedTime($infections)
    {
        return floor(0.05 * $infections);
    }

    public function casesForVentilatorsByRequestedTime($infections)
    {
        return floor(0.02 * $infections);
    }

    public function dollarsInFlight($infections, $avgIncome, $avgPopulation)
    {
       $result = $infections * $avgPopulation * $avgIncome * $this->days;
       return round($result,2);
    }

    public function setInput($data){
      $this->days                         = $this->toDays($data['timeToElapse'], $data['periodType']);
      $this->avgDailyIncomeInUSD       = $data['region']['avgDailyIncomeInUSD'];
      $this->avgDailyIncomePopulation  = $data['region']['avgDailyIncomePopulation'];
      $this->reportedCases             = $data['reportedCases'];
      $this->population                = $data['population'];
      $this->totalHospitalBeds         = $data['totalHospitalBeds'];
      $this->population                = $data['population'];
    }

    public function process($data,$severe=false)
    {
        $this->setInput($data);

        $infections= $this->infectionsByRequestedTime($this->reportedCases,$severe);

       $this->result['currentlyInfected']                  = $this->currentlyInfected;
       $this->result['infectionsByRequestedTime']          = $infections;
       $this->result['severeCasesByRequestedTime']         = $this->severeCasesByRequestedTime($infections);
       $this->result['hospitalBedsByRequestedTime']        = $this->hospitalBedsByRequestedTime(
                                                               $this->result['severeCasesByRequestedTime'],
                                                                $this->totalHospitalBeds
                                                            );
       $this->result['casesForICUByRequestedTime']         = $this->casesForICUByRequestedTime($infections);
       $this->result['casesForVentilatorsByRequestedTime'] = $this->casesForVentilatorsByRequestedTime($infections);
       $this->result['dollarsInFlight']                    = $this->dollarsInFlight($infections, $this->avgDailyIncomeInUSD,$this->avgDailyIncomePopulation);

       
        return $this->result;
    }
}
