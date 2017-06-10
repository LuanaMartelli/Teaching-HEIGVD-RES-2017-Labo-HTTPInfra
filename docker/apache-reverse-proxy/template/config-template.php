<?php
  $dynamic_app = getenv('DYNAMIC_APP');
  $static_app = getenv('STATIC_APP');
?>
<VirtualHost *:80>
  ServerName demo.res.ch

  <Proxy "balancer://dynamicapp">
    BalancerMember "http://<?php print "$dynamic_app";?>"
  </Proxy>
  
  <Proxy "balancer://staticapp">
      BalancerMember "http://<?php print "$static_app";?>"
  </Proxy>

  ProxyPass '/api/students/' 'balancer://dynamicapp/api/students/'
  ProxyPassReverse '/api/students/' 'balancer://dynamicapp/api/students/'

  ProxyPass        '/' 'balancer://staticapp/'
  ProxyPassReverse '/' 'balancer://staticapp/'
 
</VirtualHost>