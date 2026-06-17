<div class="modal fade" id="userModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="userModalLabel">
                    <i class="bi bi-person-fill-add"></i>
                    Staff
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="userForm">
                <input type="hidden" name="id" id="id">
                <div class="modal-body">
                    <div class="row mx-auto">
                        <div class="col-4">
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">First Name</label>
                                <input type="text" class="form-control" autocomplete="off" required name="firstname"
                                    id="firstname">
                            </div>
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">Designation</label>
                                <input type="text" class="form-control" autocomplete="off" required
                                     id="designation">
                            </div>

                        </div>
                        <div class="col-4">
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">Middle Name</label>
                                <input type="text" class="form-control" autocomplete="off" required name="middlename"
                                    id="middlename">
                            </div>
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">Email</label>
                                <input type="text" class="form-control" autocomplete="off" required name="email"
                                    id="email">
                            </div>

                        </div>
                        <div class="col-4">
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">Last Name</label>
                                <input type="text" class="form-control" autocomplete="off" required name="lastname"
                                    id="lastname">
                            </div>
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">Phone Number</label>
                                <input type="text" class="form-control" autocomplete="off" required
                                    id="phone_num">
                            </div>

                        </div>
                        <div class="col-12">
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">Address</label>
                                <input type="text" class="form-control" autocomplete="off" required name="address"
                                    id="address">
                            </div>
                        </div>
                        <div class="col-12">
                            <p class="text-danger mb-1 tipPassword d-none" style="font-size: 12px">Tip: If do not want to update password, leave it blank.</p>
                        </div>
                        <div class="col-4">
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">Username</label>
                                <input type="text" class="form-control" autocomplete="off" required name="username"
                                    id="username">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">Password</label>
                                <input type="password" class="form-control" autocomplete="off" required
                                    name="password" id="password">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">Confirm Password</label>
                                <input type="password" class="form-control" autocomplete="off" required
                                    id="confirm_password">
                                <p class="text-danger mb-0 d-none" id="errorPassword" style="font-size: 12px">
                                    Password do not match!</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
