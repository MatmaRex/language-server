<?php

namespace Phpactor\LanguageServer\Tests\Acceptance;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\IO\BufferIO;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

class InitializeTest extends AcceptanceTestCase
{
    public function testInitialize()
    {
        $this->sendRequest(<<<'EOT'
{                                          
     "jsonrpc": "2.0",                      
     "method": "initialize",                
     "params": {                            
         "capabilities": {                  
             "textDocument": {              
                 "completion": {            
                     "completionItem": {    
                         "snippetSupport": false                                        
                     }                      
                 }                          
             },                             
             "workspace": {                 
                 "applyEdit": true,         
                 "didChangeWatchedFiles": { 
                     "dynamicRegistration": true                                        
                 }                          
             }                              
         },                                 
         "processId": 22152,                
         "rootPath": "\/home\/daniel\/www\/phpactor\/phpactor",                         
         "rootUri": "file:\/\/\/home\/daniel\/www\/phpactor\/phpactor",                 
         "trace": "off"                     
     },                                     
     "id": 10                               
 }
EOT
        );

        $this->assertTrue($this->process()->isRunning());
        $this->assertContains('capabilities', $this->readOutput());
    }
}