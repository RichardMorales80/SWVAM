<?php if($totalPaginas >= 1): ?>

<nav class="mt-4">
<ul class="pagination justify-content-center">

<?php if($pagina > 1): ?>
<li class="page-item">
<a class="page-link" href="?pagina=<?= $pagina-1 ?>&buscar=<?= urlencode($buscar ?? '') ?>&estado=<?= $estadoFiltro ?? '' ?>">«</a>
</li>
<?php endif; ?>

<?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
<li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
<a class="page-link" href="?pagina=<?= $i ?>&buscar=<?= urlencode($buscar ?? '') ?>&estado=<?= $estadoFiltro ?? '' ?>">
<?= $i ?>
</a>
</li>
<?php endfor; ?>

<?php if($pagina < $totalPaginas): ?>
<li class="page-item">
<a class="page-link" href="?pagina=<?= $pagina+1 ?>&buscar=<?= urlencode($buscar ?? '') ?>&estado=<?= $estadoFiltro ?? '' ?>">»</a>
</li>
<?php endif; ?>

</ul>
</nav>

<?php endif; ?>