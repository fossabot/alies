<style>
/* table */
.table th {
    font-weight: 500;
    color: #827fc0;
}
.table thead {
    background-color: #f3f2f7;
}
.table>tbody>tr>td, .table>tfoot>tr>td, .table>thead>tr>td {
    padding: 14px 12px;
    vertical-align: middle;
}
.table tr td {
    color: #8887a9;
}
</style>
<div class="card shadow mb-4">

	<?php if(!isset($full_history)): ?>
	<div class="card-header"><a href="<?php echo base_url(); ?>pets/history/<?php echo $pet['id']; ?>">History</a></div>
	<?php else : ?>	
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		<div>
			<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / 
			<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet['id']; ?>"><?php echo $pet['name'] ?></a> / History
		</div>
		
		 <div class="dropdown no-arrow">
			<?php if($show_no_history == 1): ?>
			<a href="<?php echo base_url(); ?>pets/history/<?php echo $pet['id']; ?>" role="button" id="dropdownMenuLink">
				<i class="fas fa-eye"></i>
			</a>
			<?php else: ?>
			<a href="<?php echo base_url(); ?>pets/history/<?php echo $pet['id']; ?>/1" role="button" id="dropdownMenuLink">
				<i class="fas fa-eye-slash"></i>
			</a>
			<?php endif; ?>
		  </div>
	</div>
	<?php endif; ?>
	<div class="card-body">
		
		<?php if ($pet_history): ?>
		<table class="table table-hover mb-0">
		<thead>
			<tr class="align-self-center">
				<th>Type</th>
				<th>Title</th>
				<th><i class="far fa-clock"></i>  Date</th>
				<th><i class="fas fa-user-md"></i> Vet</th>
				<th><i class="fas fa-compass"></i> Location</th>
				<th>Anamnese</th>
			</tr>
		</thead>
	<?php
	$symbols = array(
			"fas fa-user-md",
			"fas fa-syringe",
			"fas fa-tooth",
			"fas fa-hospital",
			"fas fa-hammer",
			"fas fa-heartbeat",
		);
		
		for ($i = 0; $i < count($pet_history); $i++) :  
		
			$history = $pet_history[$i]; 
			$products = (isset($pet_history[$i]['products'])) ? $pet_history[$i]['products']: array();
			$procs = (isset($pet_history[$i]['procedures'])) ? $pet_history[$i]['procedures']: array();
	?>
	<tr>
		<td><div class="humb-sm rounded-circle mr-2"><i class="<?php echo $symbols[$history['type']]; ?>"></i></div></td>
		<td><?php echo $history['title']; ?></td>
		<td><?php echo substr($history['created_at'], 0, 10); ?></td>
		<td><?php echo (isset($history['vet']['first_name'])) ? $history['vet']['first_name'] : 'unknown soldier' ; ?></td>
		<td><?php echo (isset($history['location']['name'])) ? $history['location']['name'] : "unknown"; ?></td>
		<td>
			<div id="anamnese_<?php echo $i; ?>" class="btn btn-outline-secondary ana">show</div>
			<a href="<?php echo base_url(); ?>events/event/<?php echo $history['id']; ?>" class="btn btn-outline-secondary">edit</a></div>
		</td>
	</tr>
	<tr id="anamnese_<?php echo $i; ?>_text" style="display:none;">
		<td colspan="3"><?php echo nl2br ($history['anamnese']); ?></td>
		<td colspan="3" style="border-left:1px solid #e3e6f0;">
			<ul>
			<?php foreach($products as $prod) : ?>
				<li><?php echo $prod['volume'] . ' ' . $prod['unit_sell']  . ' ' . $prod['name']; ?></li>
			<?php endforeach; ?>
			<?php foreach($procs as $proc) : ?>
				<li><?php echo $proc['amount'] . ' ' . $proc['name']; ?></li>
			<?php endforeach; ?>
			</ul>
		</td>
	</tr>
	<?php endfor; ?>
		<?php if(!isset($full_history)): ?>
		<tr>
			<td colspan="6" class="text-center"><a href="<?php echo base_url(); ?>pets/history/<?php echo $pet['id']; ?>" class="btn btn-outline-secondary">Full History (<?php echo $history_count; ?>)</a></td>
		</tr>
		<?php endif; ?>
		</tbody>
	</table>
	<?php else : ?>
	No history yet.
	<?php endif; ?>
	</div>
</div>

<script type="text/javascript">

document.addEventListener("DOMContentLoaded", function(){
// history anamnese
$(".ana").click(function(){	
	$("#" + this.id + "_text").toggle();
});

});
</script>
