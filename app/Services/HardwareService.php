<?php
namespace App\Services;


/**
 * Provides service to store user meta
 *
 * @example
 * ```
 * $meta = service('user_meta');
 * $meta->save( 'key', 'string/json' );
 * $value = $meta->find( 'key' );
 * ```
 *
 * @package App\Services
 *
 */
class HardwareService
{
	protected $linux;
	
	/**
	 * This method initializes the `User` object, retrieves the user's information, and ensures the user is valid. 
	 * If the user is not valid, the process is halted with an error message. 
	 *
	 * It also initializes the appropriate `user_meta`
	 *
	 * @throws Exception If the user data is invalid or missing, the action will be halted with an error message.
	 */
	public function __construct( )
	{
		$this->linux = is_readable("/proc/stat");
	}

	private function convertKb(int $size): string
	{
		if ($size <= 0) {
			return '0 KB';
		}

		$bytes = $size * 1024;

		$units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
		$i = floor(log($bytes, 1024));

		return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
	}
		
	public function readCpuStats(): array
    {
        $stats = [];
		
		if ( !$this->linux )
			return $stats;

        foreach (file('/proc/stat') as $line) {
            if (preg_match('/^cpu(\d+)\s+(.*)$/', $line, $m)) {
                $stats[(int) $m[1]] = array_map(
                    'intval',
                    preg_split('/\s+/', trim($m[2]))
                );
            }
        }

        return $stats;
    }
	
	public function getPerCoreCPULoad(float $interval = 1.0): array
    {
		$usage = [];
		
		if ( !$this->linux )
			return $usage;
		
        $first  = $this->readCpuStats();
        usleep((int) ($interval * 1000000));
        $second = $this->readCpuStats();

        foreach ($first as $core => $v1) {
            if (!isset($second[$core])) {
                continue;
            }

            $v2 = $second[$core];

            // idle + iowait
            $idle1 = $v1[3] + ($v1[4] ?? 0);
            $idle2 = $v2[3] + ($v2[4] ?? 0);

            $total1 = array_sum($v1);
            $total2 = array_sum($v2);

            $totalDiff = $total2 - $total1;
            $idleDiff  = $idle2 - $idle1;

            $usage[$core] = $totalDiff > 0
                ? round((1 - ($idleDiff / $totalDiff)) * 100, 2)
                : 0.0;
        }

        return $usage;
    }
	
	public function getOSVersion( ){
        $OsVersion = shell_exec ( 'uname -a' );

        return $OsVersion;
    }
	
    public function getHWVersion( ){
        $version = '';

		if ( $this->linux && is_readable('/proc/device-tree/model') )
		  	$version = @file_get_contents('/proc/device-tree/model');

		return $version;
    }
	
    public function getCPUTemperature( ){
        $temperature = 0;

        if ( $this->linux && is_readable('/sys/class/thermal/thermal_zone0/temp')){
            $str            = @file_get_contents('/sys/class/thermal/thermal_zone0/temp');
            $temperature    = intval($str) / 1000;
            }

        return $temperature;
    }
	
	public function getMemoryUsage(): array
	{
		if ( !$this->linux )
			return [];

		$contents = file_get_contents('/proc/meminfo');
		preg_match_all('/(\w+):\s+(\d+)/', $contents, $matches);
		$mem = array_combine($matches[1], $matches[2]);

		$total = (int) ($mem['MemTotal'] ?? 0);
		$free  = (int) ($mem['MemAvailable'] ?? 0);

		if ($total === 0) {
			return [];
		}

		$used = $total - $free;
		//$used  = $total - ($mem['MemFree'] + $mem['Buffers'] + $mem['Cached']);
		$usedPercent = round(($used / $total) * 100, 2);

		return [
			'total_kb'   => $this->convertKb($total),
			'free_kb'    => $this->convertKb($free),
			'used_kb'    => $this->convertKb($used),
			'used_pct'   => $usedPercent
		];
	}
	
	public function getAll(): array
	{
		$data = [
			"os_version" 	=> $this->getOSVersion(),
			"hw_version" 	=> $this->getHWVersion(),
			"cpu_temp" 		=> $this->getCPUTemperature(),
			"cpu_load" 		=> $this->getPerCoreCPULoad(),
			"memory" 		=> $this->getMemoryUsage(),
		];
		$data["cpu_cores"] = count($data["cpu_load"]);
		
		return $data;
	}
}