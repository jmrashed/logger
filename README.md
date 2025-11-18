# Development Logger Library

A lightweight, production-ready logging utility for development environments.

## Features

- Multiple log levels (DEBUG, INFO, WARNING, ERROR, CRITICAL)
- Automatic log rotation (10MB max, keeps 5 files)
- Context data support
- Thread-safe file writing
- Git-ignored log files

## Usage

```php
// Include the logger

require_once $_SERVER['DOCUMENT_ROOT'] . '/_Logger/Logger.php';

// Basic logging
Logger::info('User logged in');
Logger::error('Database connection failed');
Logger::warning('Deprecated function used');

// Logging with context
Logger::info('User action', ['user_id' => 123, 'action' => 'login']);
Logger::error('Query failed', ['query' => 'SELECT * FROM users', 'error' => $e->getMessage()]);
```

## Log Levels

- `DEBUG` - Detailed debug information
- `INFO` - General information messages
- `WARNING` - Warning messages
- `ERROR` - Error conditions
- `CRITICAL` - Critical conditions

## Log Format

```
[2024-01-15 14:30:25] [INFO] User logged in {"user_id":123,"action":"login"}
```

## File Structure

```
_Logger/
├── Logger.php          # Main logger class
├── logs/              # Log files directory (git-ignored)
│   ├── .gitkeep       # Keeps directory in git
│   └── application.log # Main log file
├── .gitignore         # Excludes logs from git
└── README.md          # This file
```

## Configuration

The logger automatically:
- Creates log directory if it doesn't exist
- Rotates logs when they exceed 10MB
- Keeps maximum 5 log files
- Uses thread-safe file writing


# Example of Usages


```php
require_once $_SERVER['DOCUMENT_ROOT'] . '/_Logger/Logger.php';
function call_api($api_file_name, $param_string="", $pagination=1)
{

    if ($api_file_name!='')
    {
        Logger::info("Initiating API call", ['api_file_name' => $api_file_name, 'param_string' => $param_string, 'pagination' => $pagination]);
       $myHITurl = REMOTESERVER."admin/api-response/$api_file_name?api_key=".APIKEY.$param_string."&pagination=".$pagination;
        $timeout = 0; // 100; // set to zero for no timeout
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $myHITurl );
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
        curl_setopt($ch, CURLOPT_FILETIME, true);
        $data_raw = curl_exec ($ch);
        if (curl_error($ch)) {
            Logger::error("API call failed", ['error' => curl_error($ch), 'url' => $myHITurl]);
            curl_close($ch);
            return null;
        } else {
            $data = json_decode($data_raw, true);
            Logger::info("API call successful", ['api_file_name' => $api_file_name]);
            curl_close ( $ch );
            return $data;
        }
    } else {
        Logger::warning("API call attempted with empty api_file_name");
        return null;
    }

}
```


# Sample Output 

```bash
[2025-11-18 08:13:14] [INFO] Applicant data loaded {"ref":"aea7025d-c774-47f6-83eb-03168b14f4de","success":null,"applicant_id":"not set"}
[2025-11-18 08:15:05] [INFO] Applicant data loaded {"ref":"aea7025d-c774-47f6-83eb-03168b14f4de","success":null,"applicant_id":"not set"}
[2025-11-18 08:15:10] [INFO] Applicant data loaded {"ref":"aea7025d-c774-47f6-83eb-03168b14f4de","success":null,"applicant_id":"not set"}[2025-11-18 13:19:49] [INFO] Initiating API call {"api_file_name":"fetch-portal-information.php","param_string":"","pagination":1}
[2025-11-18 13:19:49] [INFO] API call successful {"api_file_name":"fetch-portal-information.php"}
[2025-11-18 13:19:49] [INFO] Initiating API call {"api_file_name":"search-applicant-pre-application-checker.php","param_string":"&tool=applicant-interview-booking-history&ref=524635fd-a451-4767-89a6-2ac6d413a170&r=1763471989721&portal_id=agent&portal_user=10173&page=&user_type=agent&reporting_to_agent_id=&agent_sub_role=","pagination":1}
[2025-11-18 13:19:49] [INFO] API call successful {"api_file_name":"search-applicant-pre-application-checker.php"}
[2025-11-18 13:19:49] [INFO] Initiating API call {"api_file_name":"applicant.php","param_string":"&id=524635fd-a451-4767-89a6-2ac6d413a170","pagination":1}
[2025-11-18 13:19:50] [INFO] API call successful {"api_file_name":"applicant.php"}
[2025-11-18 13:19:50] [INFO] Initiating API call {"api_file_name":"applicant-interview-booking-history.php","param_string":"&ref=524635fd-a451-4767-89a6-2ac6d413a170","pagination":1}
[2025-11-18 13:19:50] [INFO] API call successful {"api_file_name":"applicant-interview-booking-history.php"}
[2025-11-18 13:19:52] [INFO] Initiating API call {"api_file_name":"fetch-portal-information.php","param_string":"","pagination":1}
[2025-11-18 13:19:52] [INFO] API call successful {"api_file_name":"fetch-portal-information.php"}
[2025-11-18 13:19:52] [INFO] Initiating API call {"api_file_name":"search-applicant-pre-application-checker.php","param_string":"&tool=applicant-campus-discovery&student_id=208936&ref=524635fd-a451-4767-89a6-2ac6d413a170&r=1763471991991&portal_id=agent&portal_user=10173&page=&user_type=agent&reporting_to_agent_id=&agent_sub_role=","pagination":1}
[2025-11-18 13:19:52] [INFO] API call successful {"api_file_name":"search-applicant-pre-application-checker.php"}
[2025-11-18 13:19:52] [INFO] Initiating API call {"api_file_name":"applicant.php","param_string":"&id=524635fd-a451-4767-89a6-2ac6d413a170","pagination":1}
[2025-11-18 13:19:52] [INFO] API call successful {"api_file_name":"applicant.php"}
[2025-11-18 13:19:52] [INFO] Initiating API call {"api_file_name":"applicant-campus-discovery-progress.php","param_string":"&student_id=208936","pagination":1}
[2025-11-18 13:19:52] [INFO] API call successful {"api_file_name":"applicant-campus-discovery-progress.php"}
[2025-11-18 13:19:52] [INFO] Initiating API call {"api_file_name":"applicant-campus-discovery-consent.php","param_string":"&student_id=208936","pagination":1}
[2025-11-18 13:19:52] [INFO] API call successful {"api_file_name":"applicant-campus-discovery-consent.php"}
[2025-11-18 13:19:52] [INFO] Initiating API call {"api_file_name":"applicant-campus-discovery-eligibility.php","param_string":"&prospective_student_id=208936&agent_id=10173","pagination":1}
[2025-11-18 13:19:52] [INFO] API call successful {"api_file_name":"applicant-campus-discovery-eligibility.php"}
```