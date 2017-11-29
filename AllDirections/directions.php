<?php

class Directions
{
    private $rawFile;
    private $parsedFile;
    private $calculatedData;
    private $testCaseCount = 0;

    /**
     * Processes input and outputs results
     * @param string $data
     * @return void
     */
    public function processData(string $data)
    {
        $this->readData($data);
        $this->parseData();
        $this->calculateData();
        $this->outputData();

    }

    /**
     * @param string $data
     */
    private function readData(string $data)
    {
        $this->rawFile = explode("\n", $data);
        if (!$this->rawFile) {
            exit("Error loading file");
        }
    }

    /**
     * Parses file into usable data structure
     */
    private function parseData()
    {
        foreach ($this->rawFile as $line) {
            $line = explode(' ', $line);

            if (count($line) == 1) {
                $personCount = $line[0];

                if ($personCount > 0) {
                    $this->testCaseCount++;
                    $this->parsedFile[$this->testCaseCount][$personCount] = [];
                }
            } else {
                $this->parsedFile[$this->testCaseCount][$personCount][] = $line;
            }
        }
    }

    /**
     * Makes all calculations
     */
    private function calculateData()
    {
        foreach ($this->parsedFile as $testCaseNumber => $testCases) {
            $this->calculatedData[$testCaseNumber] = $this->proceedTestCase($testCases);
        }
    }

    /**
     * Outputs calculated data
     */
    private function outputData()
    {
        $output = '';
        foreach ($this->calculatedData as $data) {
            $output .= round($data[0][0], 4) . ' ' . round($data[0][1], 4) . ' ' . round($data[1], 5) . "\r\n";
        }

        echo $output;
    }

    /**
     * Iterates through routes to calculate each
     *
     * @param array $testCases
     * @return array
     */
    private function proceedTestCase(array $testCases)
    {
        foreach ($testCases as $testCase) {
            foreach ($testCase as $personData) {
                $calculatedTestCase[] = [
                    'start_coordinates' => array_slice($personData, 0, 2),
                    'route' => array_slice($personData, 2)
                ];
            }
        }
        $xSum = 0;
        $ySum = 0;
        foreach ($calculatedTestCase as $test) {
            $results[] = $this->proceedRoute($test);
        }
        foreach ($results as $result) {
            $resultingCalculation[] = $result;
            $xSum += $result[0];
            $ySum += $result[1];
        }

        // Getting average endpoint coordinates
        $averagePoint = [($xSum / count($calculatedTestCase)), ($ySum / count($calculatedTestCase))];
        //Gets biggest distance
        $distance = 0;
        foreach ($resultingCalculation as $point) {
            $currentDistance = $this->getDistance($averagePoint, $point);

            if ($currentDistance > $distance) {
                $distance = $currentDistance;
            }
        }

        return [$averagePoint, $distance];
    }

    /**
     * Proceeds route of certain person
     *
     * @param array $test
     * @return array
     */
    private function proceedRoute(array $test)
    {
        $commands = array_values(array_filter($test['route'], function ($key) {
            return !($key & 1);
        }, ARRAY_FILTER_USE_KEY));
        $parameters = array_values(array_filter($test['route'], function ($key) {
            return $key & 1;
        }, ARRAY_FILTER_USE_KEY));
        $commandNumber = count($parameters);
        $point = $test['start_coordinates'];
        $point[2] = 0;
        for ($i = 0; $i < $commandNumber; $i++) {
            $point = $this->getPoint($commands[$i], $parameters[$i], $point);
        }

        return $point;
    }

    /**
     * Executes route command
     *
     * @param string $command
     * @param $parameter
     * @param array $point
     * @return array
     */
    private function getPoint(string $command, $parameter, array $point)
    {
        // Gets changes in coordinates
        if ($command == 'turn' || $command == 'start') {
            $point[2] += $parameter;
        } else {
            $point[0] += $parameter * cos(deg2rad($point[2]));
            $point[1] += $parameter * sin(deg2rad($point[2]));
        }

        return $point;
    }

    /**
     * Gets distance between two points
     * @param $point
     * @param $endPoint
     * @return float
     */
    private function getDistance(array $point, array $endPoint)
    {
        $distance = sqrt(pow(($point[0] - $endPoint[0]), 2) + pow(($point[1] - $endPoint[1]), 2));
        return $distance;
    }

}

$directions = new Directions();
$data = stream_get_contents(STDIN);
$directions->processData($data);
