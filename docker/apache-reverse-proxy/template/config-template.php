<?php
  $dynamic_app1 = getenv('DYNAMIC_APP1');
  $dynamic_app2 = getenv('DYNAMIC_APP2');
  $static_app1 = getenv('STATIC_APP1');
  $static_app2 = getenv('STATIC_APP2');
?>
<VirtualHost *:80>
  ServerName demo.res.ch
  
  <Proxy balancer://staticapp>
      BalancerMember "http://<?php print "$static_app1"?>"
      BalancerMember "http://<?php print "$static_app2"?>"
    ProxySet lbmethod=byrequests
  </Proxy>

  <Proxy balancer://dynamicapp>
    BalancerMember "http://<?php print "$dynamic_app1"?>"
    BalancerMember "http://<?php print "$dynamic_app2"?>"
    ProxySet lbmethod=byrequests
  </Proxy>

  ProxyPass '/api/students/' 'balancer://dynamicapp/'
  ProxyPassReverse '/api/students/' 'balancer://dynamicapp/'

  ProxyPass '/' 'balancer://staticapp/'
  ProxyPassReverse '/' 'balancer://staticapp/'
 
</VirtualHost>