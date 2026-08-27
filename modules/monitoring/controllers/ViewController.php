<?php
class ViewController {
    
    public function dashboard() {
        include '../views/dashboard.php';
    }
    
    public function attendance() {
        include '../views/attendance.php';
    }
    
    public function facilities() {
        include '../views/facilities.php';
    }
    
    public function visitors() {
        include '../views/visitors.php';
    }
    
    public function reports() {
        include '../views/reports.php';
    }
    
    public function onlineClasses() {
        include '../views/online-classes.php';
    }
    
    public function archive() {
        include '../views/archive.php';
    }
    
    public function register() {
        include '../views/register.php';
    }
    
    public function timeout() {
        include '../views/timeout.php';
    }
    
    public function qrcode() {
        include '../views/qrcode.php';
    }
    
    // ============================================
    // MOBILE ATTENDANCE VIEW
    // ============================================
    public function mobileAttendance() {
        include '../views/mobile-attendance.php';
    }

    public function mobileFacilities() {
        include '../views/mobile-facilities.php';
    }
}