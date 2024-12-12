<?php
/**
* Git Commits Visualizer
* 通过 GitHub/GitLab 公开 API 获取提交信息并生成 SVG 可视化
*/
define('GITVIZ_VERSION', '1.0.0');
define('GITVIZ_LOG_PATH', '/tmp/gitviz-logs');
define('GITVIZ_LOG_FILE', GITVIZ_LOG_PATH . '/gitviz-' . date('Y-m-d') . '_' . uniqid() . '.log');
define('GITVIZ_DEBUG', false);
define('GITVIZ_CACHE_DURATION', 300); // 缓存持续时间（秒）
define('GITVIZ_RATE_LIMIT_DURATION', 30); // 频率限制时间窗口（秒）
define('GITVIZ_RATE_LIMIT_MAX_REQUESTS', 5); // 频率限制最大请求数
define('GITVIZ_CACHE_PATH', '/tmp/gitviz-cache');
class Logger {
private $logFile;
public function __construct($logFile = null) {
$this->logFile = $logFile ?? GITVIZ_LOG_FILE;
if (!is_dir(dirname($this->logFile))) {
mkdir(dirname($this->logFile), 0755, true);
}
}
public function log($message, $level = 'INFO') {
$timestamp = date('Y-m-d H:i:s');
$logEntry = sprintf("[%s] [%s] %s\n", $timestamp, $level, $message);
if (GITVIZ_DEBUG) {
error_log($logEntry);
}
file_put_contents($this->logFile, $logEntry, FILE_APPEND);
}
}
class CacheManager {
private $cacheDir;
public function __construct() {
$this->cacheDir = GITVIZ_CACHE_PATH;
if (!is_dir($this->cacheDir)) {
mkdir($this->cacheDir, 0755, true);
}
}
private function getCacheKey($repoUrl, $branch, $limit) {
return md5($repoUrl . $branch . $limit);
}
private function getCacheFile($key) {
return $this->cacheDir . '/' . $key . '.cache';
}
public function get($repoUrl, $branch, $limit) {
$key = $this->getCacheKey($repoUrl, $branch, $limit);
$file = $this->getCacheFile($key);
if (file_exists($file) && (time() - filemtime($file)) < GITVIZ_CACHE_DURATION) {
return unserialize(file_get
