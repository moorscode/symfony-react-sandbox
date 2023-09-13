import React, {useEffect} from "react";
import Actions from "../actions/recipesActions";
import {connect} from "react-redux";
import RecipeWidget from "../../common/components/Recipe";
import {Link, useParams} from "react-router-dom";

const Recipe = ({recipe, fetching, dispatch, baseUrl}) => {
    const {id} = useParams();

    useEffect(() => {
        if (
            !recipe ||
            recipe.id !== parseInt(id, 10)
        ) {
            dispatch(
                Actions.fetchRecipe(id, baseUrl)
            );
        }
    }, [dispatch, baseUrl, id, recipe]);

    // if we know that we are loading that
    if (
        fetching ||
        // or we do not have a recipe
        !recipe ||
        // or the recipe we have is not the one we should have
        recipe.id !== parseInt(id, 10)
    ) {
        return <div>Loading...</div>;
    }

    return (
        <div>
            <ol className="breadcrumb">
                <li>
                    <Link to={"/"}>Recipes</Link>
                </li>
                <li className="active">{recipe.name}</li>
            </ol>
            <RecipeWidget recipe={recipe}/>
        </div>
    );
}

const mapStateToProps = state => ({
    recipe: state.recipesState.recipe,
    fetching: state.recipesState.fetching,
    baseUrl: state.recipesState.baseUrl
});

export default connect(mapStateToProps)(Recipe);
