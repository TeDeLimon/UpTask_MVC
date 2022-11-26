<div class="contenedor reestablecer">
    <?php include_once __DIR__ . '/../templates/nombre-sitio.php'; ?>

    <div class="contenedor-sm">
        <p class="descripcion-pagina">Coloca tu nuevo Password</p>

        <?php include_once __DIR__ .'/../templates/alertas.php'; ?>

        <?php if($mostrar): ?>
            <form class="formulario" method="POST">

                <div class="campo">
                    <label for="password">Contraseña</label>
                    <input 
                        type="password"
                        id="password"
                        placeholder="Tu contraseña"
                        name="password"
                    />
                </div>

                <div class="campo">
                    <label for="password"2>Repita la Contraseña</label>
                    <input 
                        type="password"
                        id="password2"
                        placeholder="Tu contraseña"
                        name="password2"
                    />
                </div>

                <input type="submit" class="boton" value="Guardar Password"/>

            </form>
        <?php endif?>
        
        <div class="acciones">
            <a href="/crear">¿Aún no tienes una cuenta? Obtener una</a>
            <a href="/olvide">¿Olvidaste tu password?</a>
        </div>
    </div>
</div>