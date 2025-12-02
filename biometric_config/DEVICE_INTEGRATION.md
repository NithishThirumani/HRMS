# Biometric Device Integration Guide

This document explains how to integrate specific biometric devices with the EMPS system by replacing the placeholder code in the `device_manager.php` file with actual device SDK code.

## Overview

The current implementation includes a generic `BiometricDeviceManager` class that provides a framework for communicating with biometric devices. To integrate with a specific device, you'll need to modify this class to use the device's SDK or communication protocol.

## Integration Steps

1. **Identify Your Device SDK**: Determine which SDK or communication protocol your biometric device uses. Common manufacturers include:
   - ZKTeco
   - Suprema
   - Anviz
   - HID
   - Fingertec

2. **Obtain SDK Documentation**: Contact the device manufacturer to obtain SDK documentation and any required libraries.

3. **Install Required Libraries**: Install any required libraries or dependencies for your device's SDK.

4. **Modify the BiometricDeviceManager Class**: Replace the placeholder methods in `device_manager.php` with actual SDK calls.

## Common Device Types and Integration Examples

### ZKTeco Devices

ZKTeco devices typically use the ZKLib PHP library for communication. Here's how to modify the `connect()` method:

```php
/**
 * Connect to the biometric device
 * 
 * @return bool Connection status
 */
public function connect() {
    // Include ZKLib
    require_once 'path/to/zklib/ZKLib.php';
    
    // Create ZKLib instance
    $this->zk = new ZKLib($this->ip, $this->port, $this->timeout);
    
    // Connect to device
    $connected = $this->zk->connect();
    
    if ($connected) {
        // Set communication password if needed
        if ($this->comm_key !== 0) {
            $this->zk->setCommPassword($this->comm_key);
        }
    }
    
    return $connected;
}
```