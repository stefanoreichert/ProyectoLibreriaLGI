        </div>
    </main>
    
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4><?php echo SITE_NAME; ?></h4>
                <p>Sistema de gestión integral para bibliotecas y librerías</p>
            </div>
            
            <div class="footer-section">
                <h4>Información</h4>
                <ul>
                    <li>Versión: <?php echo SITE_VERSION; ?></li>
                    <li>Desarrollado por: <?php echo DEVELOPED_BY; ?></li>
                    <li>Año: <?php echo date('Y'); ?></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Soporte</h4>
                <ul>
                    <li><a href="docs/README.md">Documentación</a></li>
                    <li><a href="mailto:soporte@libreria-lgi.com">Contacto</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Todos los derechos reservados.</p>
        </div>
    </footer>
    
    <script src="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>assets/js/main.js"></script>
    <script src="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>assets/js/validaciones.js"></script>
    <?php if (isset($include_search_js) && $include_search_js): ?>
    <script src="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>assets/js/busquedas.js"></script>
    <?php endif; ?>
</body>
</html>