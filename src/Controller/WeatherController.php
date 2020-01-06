<?php
    namespace App\Controller;

    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    class WeatherController extends AbstractController
    {
        /**
         * @Route ("/")
         */

         public function index()
         {
            // Get the user city
            $user_ip = getenv('REMOTE_ADDR'); // Determine the user_ip variable
            $geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=$user_ip"));
            $user_city = $geo["geoplugin_city"]; // Determine the user_city variable

            // Read data from city.list.json file
            $city_str = file_get_contents('../config/city.list.json');
            $city_json = json_decode($city_str); // Decode the JSON
            foreach ($city_json as $item) {
                if ($item->name == $user_city) { // Compare user's city with listed cities
                    $found_city_id = $item->id; // Determine the user's citie id
                }
            }

            // Get the city weather info from openweathermap API
            $apiKey = "88fb38129bea9331d7406e9243f4f453";
            $cityId = $found_city_id;
            $googleApiUrl = 'http://api.openweathermap.org/data/2.5/weather?id=' . $cityId . '&lang=en&units=metric&APPID=' . $apiKey;

            $ch = curl_init(); // Initialize cURL session

            // Set cURL session options
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $googleApiUrl);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $weather_answer = curl_exec($ch); // Perform a cURL session with given options

            curl_close($ch); // Exit a cURL session
            $data = json_decode($weather_answer); // Get the weather data of given city
            $currentTime = time(); // Ger current time


            // Set a template to return and set it options
             return $this->render("weather.html.twig", [
                'city' => $data->name,
                'date_currentTime' => date("l g:i a", $currentTime),
                'date_currentDate' => date("jS F, Y",$currentTime),
                'weather_description' => ucwords($data->weather[0]->description),
                'icon' => $data->weather[0]->icon,
                'temp_max' => $data->main->temp_max,
                'temp_min' => $data->main->temp_min,
                'humidity' => $data->main->humidity,
                'wind_speed' => $data->wind->speed
             ]);
         }
    }
?>