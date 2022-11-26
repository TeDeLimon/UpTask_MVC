<?php include_once __DIR__ .'/header-dashboard.php'; ?>
    <div class="contenedor-sm">
        <?php include_once __DIR__ . '/../templates/alertas.php'; ?>

        <a href="/perfil" class="enlace">Volver al perfil</a>

        <form class="formulario" method="POST" action="/cambiar-password">
            <div class="campo">
                <label for="password_actual">Password Actual</label>
                <input
                    type="password"
                    name="password_actual"
                    id="nombre"
                    placeholder="Tu Password actual"
                />
            </div>
            <div class="campo">
                <label for="password_nuevo">Password Nueva</label>
                <input
                    type="password"
                    id="password_nuevo"
                    name="password_nuevo"
                    placeholder="Tu Password Nueva"
                />
            </div>
            <input type="submit" value="Guardar Cambio">
        </form>
    </div>
<?php include_once __DIR__ .'/footer-dashboard.php'; ?>